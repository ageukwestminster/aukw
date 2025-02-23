<?php

namespace Models;

class QBItemSalesReport extends QuickbooksReport{

  /** Execute the itemSales report in QBO */
  function queryQuickBooks() : mixed {

    $this->report = $this->reportService
        ->setStartDate($this->startdate)
        ->setEndDate($this->enddate)
        ->setSummarizeColumnBy($this->summarizeColumn)
        ->setItem($this->item)
        ->executeReport(\QuickBooksOnline\API\ReportService\ReportName::ITEMSALES);

    return $this->report;
  }

  /**
   * Generate a listing of sales by Item (aka Product)
   * @return array
   * 
   */
  public function adaptReport() : array {

    $itemSales = $this->report;

    $returnObj = array();

    /** @disregard Intelephense error on next line */
    if ($itemSales && property_exists($itemSales, 'Rows') 
                        && property_exists($itemSales->Rows, 'Row')) {
        /** @disregard Intelephense error on next line */
        $dataArray = $itemSales->Rows->Row;

        if (is_array($dataArray)) {
            // Loop through the array and extract the rows of interest
            foreach ($dataArray as $value) {
                // Presence of ColData here indicates a simple row
                if (property_exists($value, 'ColData') ) {
                    $row = $value->ColData;
                    if (is_array($row)) {
                        $first = $row[0];
                        if (in_array($first->value, array('Book Ragging', 'Ragging Sales'))) {
                            array_push($returnObj, array(
                                "id" => (property_exists($first, 'id')?$first->id:0),
                                "name" => $first->value,
                                "number" => $row[1]->value,
                                "amount" => $row[2]->value,
                                "avgprice" => $row[4]->value,
                                "israg" => true,
                            ));
                        }
                    }
                } elseif (property_exists($value, 'Rows')) {
                    $products=array();  
                    $rag = false;                      
                    // Presence of Rows indicates an item/subitem relationshoip in QB
                    // Either Ragging or Daily Sales
                    if (property_exists($value, 'Header')) {
                        if ($value->Header->ColData[0]->value=='Daily Sales') {
                            $products = ['Books','Bric-a-Brac','Clothing','Linens','Donations', 'Ragging'];
                        } else {
                            $products = ['Books','Clothing','Household Items','Rummage (HHR)', 'Shoes'];
                            $rag = true;
                        }
                    }

                    // Need to go down to level of Rows->Row[1..n]->ColData to process
                    if (property_exists($value->Rows, 'Row')) {
                        $group = $value->Rows->Row;
                        if (is_array($group)) {
                            foreach ($group as $groupMember) {
                                if (property_exists($groupMember, 'ColData') ) {
                                    $row = $groupMember->ColData;
                                    if (is_array($row)) {
                                        $first = $row[0];
                                        if (in_array($first->value, $products)) {

                                            array_push($returnObj, array(
                                                "id" => (property_exists($first, 'id')?$first->id:0),
                                                "name" => $first->value,
                                                "number" => $row[1]->value,
                                                "amount" => $row[2]->value,
                                                "avgprice" => $row[4]->value,
                                                "israg" => $first->value=='Ragging'?true:$rag,
                                            ));

                                        }
                                    }
                                }
                            }
                        }                            
                    }
                }
            }

            return $returnObj;
        } else {
            http_response_code(422);  
            return array(
                "message" => "QB result set is not an array",
            );
        }


    } else {
        http_response_code(422);  
        return array(
            "message" => "QB returned null value",
        );
    }                    
    
  }

  /**
   * Note: to understand the logic it might help to see the report at the QBO website
   * The report is called "Sales by Product/Service Summary Report" and is found in "Standard Reports"
   * Set the start and end dates and set the "Display columns by" to Fiscal Quarters.
   */
  public function extractRaggingNumbers() : array{

    $itemSales = $this->report;

    $returnObj = array();

    // Loop through the Columns section to get the start and end date of each quarter
    /** @disregard Intelephense error on next line */
    if ($itemSales && property_exists($itemSales, 'Columns') 
                      && property_exists($itemSales->Columns, 'Column')) {

      foreach($itemSales->Columns->Column as $column) {
        if ($column->ColTitle == '' || $column->ColTitle == 'Total') {
          continue;
        }

        $quarter = new RaggingQuarter;
        $quarter->title = $column->ColTitle;

        // Dive further into the $column object
        foreach($column->Columns->Column as $quarterColumn) {
          if ($quarterColumn->ColTitle == 'Quantity') {
            $metadataArray = $quarterColumn->MetaData;
            foreach($metadataArray as $metadata) {
              if($metadata->Name == 'StartDate') {
                $quarter->start = $metadata->Value;
                continue;
              } else if ($metadata->Name == 'EndDate') {
                $quarter->end = $metadata->Value;
                continue;
              }
            }
            
          }
        }

        $returnObj[] = $quarter;
      }
    } else {
      throw new \Exception ('Unable to locate Columns->Column section of ItemSales report.');
    }

    $numberOfQuarters = count($returnObj);

    // Loop through the Rows section to fill in the data for each quarter, by item
    if ($itemSales && property_exists($itemSales, 'Rows') 
            && property_exists($itemSales->Rows, 'Row')) {
      foreach($itemSales->Rows->Row as $row) {

        // Handle old style Ragging
        if (property_exists($row, 'ColData') ) {          
          if ($row->ColData[0]->value == 'Book Ragging') {
            for ($i = 0; $i < $numberOfQuarters; $i++) {
              $quarter = $returnObj[$i];
              $quarter->books->amount += (float)$row->ColData[2+$i*4]->value;
            }
            continue;
          } else if ($row->ColData[0]->value == 'Ragging Sales') {
            for ($i = 0; $i < $numberOfQuarters; $i++) {
              $quarter = $returnObj[$i];
              $quarter->clothing->amount += (float)$row->ColData[2+$i*4]->value;
            }
            continue;
          }
        }

        // Handle Ragging that has been records as part of daily takings process
        if (property_exists($row, 'Header') && property_exists($row->Header, 'ColData')
            && is_array($row->Header->ColData) && count($row->Header->ColData)
            && $row->Header->ColData[0]->value == 'Daily Sales') {  
          foreach($row->Rows->Row as $dailySalesRow) {
            if (property_exists($dailySalesRow, 'ColData') 
                && is_array($dailySalesRow->ColData) && count($dailySalesRow->ColData)
                && $dailySalesRow->ColData[0]->value == 'Ragging') {
              for ($i = 0; $i < $numberOfQuarters; $i++) {
                $quarter = $returnObj[$i];
                $quarter->clothing->amount += (float)$dailySalesRow->ColData[2+$i*4]->value;
              }
            }          
          }
        }

        //Handle the current way of booking Ragging using Products
        if (property_exists($row, 'Header') && property_exists($row->Header, 'ColData')
            && is_array($row->Header->ColData) && count($row->Header->ColData)
            && $row->Header->ColData[0]->value == 'Ragging') {  
          foreach($row->Rows->Row as $raggingRow) {
            if (property_exists($raggingRow, 'ColData')) {

              // Given the Row heading, deduce the name of the RaggingQuarter property
              switch ($raggingRow->ColData[0]->value) {
                case 'Books':
                  $propertyName = 'books';
                  break;
                case 'Clothing':
                  $propertyName = 'clothing';
                  break;
                case 'Household Items':
                  $propertyName = 'household';
                  break;
                case 'Rummage (HHR)':
                  $propertyName = 'rummage';
                  break;
                case 'Shoes':
                  $propertyName = 'shoes';
                  break;              
                default:
                  $propertyName = 'other';
                  break;     
                }

              for ($i = 0; $i < $numberOfQuarters; $i++) {                    
                $quarter = $returnObj[$i];
                $amount = (float)$raggingRow->ColData[2+$i*4]->value;
                if ($amount) {
                  // Note the special syntax: object->{property_name}
                  // The curly brace syntax allows you to use a string literal or 
                  // variable as a property or method name.
                  $quarter->{$propertyName}->amount += $amount;
                  $quarter->{$propertyName}->quantity = (float)$raggingRow->ColData[1+$i*4]->value;
                  $quarter->{$propertyName}->avgprice = (float)$raggingRow->ColData[4+$i*4]->value;
                }
              }
            }          
          }
        }
      }
    } else {
      throw new \Exception ('Unable to locate Rows->Row section of ItemSales report.');
    }


    return $returnObj;
  }
}

class RaggingItem{ 
  public float $quantity = 0;
  public float $amount = 0;
  public float $avgprice = 0;
}
class RaggingQuarter{
  public string $title = '';
  public string $start = '';
  public string $end = '';
  public RaggingItem $books;
  public RaggingItem $clothing;
  public RaggingItem $household;
  public RaggingItem $rummage;
  public RaggingItem $shoes;
  public RaggingItem $other;

  function __construct() {
    $this->books = new RaggingItem;
    $this->clothing = new RaggingItem;
    $this->household = new RaggingItem;
    $this->rummage = new RaggingItem;
    $this->shoes = new RaggingItem;
    $this->other = new RaggingItem;
  }
}