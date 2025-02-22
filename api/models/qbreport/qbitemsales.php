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

  public function extractRaggingNumbers() : array{
    $returnObj = array();
    return $returnObj;
  }
}