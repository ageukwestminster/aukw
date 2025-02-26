<?php

namespace Models;

use \QuickBooksOnline\API\ReportService\ReportName;

class QBProfitAndLossReport extends QuickbooksReport{

  /** Execute the Profit & Loss report in QBO */
  function queryQuickBooks() : mixed {

    $this->report = $this->reportService
    ->setStartDate($this->startdate??'')
    ->setEndDate($this->enddate??'')
    ->setSummarizeColumnBy($this->summarizeColumn??'')
    ->setDateMacro($this->dateMacro??'')
    ->setColumns($this->columns)
    //->setSortBy($this->sortBy) // Not valid for PNL report. Use PNLDetail if needed.
    ->executeReport(ReportName::PROFITANDLOSS);

    return $this->report;
  }

      /**
     * Convert the raw QuickBooks Profit and Loss report into a simpler format.
     * @param array $profitAndLossReport A report object in raw form, received from QuickBooks
     * @return mixed
     */
    public function adaptReport() : array {

      $pnlReport = $this->report;

      $report=array();
      $report['data'] = array();
      
      /** @disregard Intelephense error on next line */
      if ($pnlReport && property_exists($pnlReport, 'Header')
              && property_exists($pnlReport->Header, 'StartPeriod')) {
          /** @disregard Intelephense error on next line */
          $report['start'] = $pnlReport->Header->StartPeriod;
          /** @disregard Intelephense error on next line */
          $report['end']  = $pnlReport->Header->EndPeriod;
      } else {
          http_response_code(422);  
          return array(
              "message" => "QBO P&L report is missing Header or StartPeriod.",
          );
          exit(1);
      }

      /** @disregard Intelephense error on next line */
      if ($pnlReport && property_exists($pnlReport, 'Rows')
               && property_exists($pnlReport->Rows, 'Row')) {

          /** @disregard Intelephense error on next line */
          $dataArray = $pnlReport->Rows->Row;

          foreach ($dataArray as $rowItem) {

              $summaryItem = $rowItem->Summary;
              $sectionName = strtolower($rowItem->group);
              $report['data'][$sectionName] = array();

              $columnValues = $summaryItem->ColData;
              $report['data'][$sectionName]['name']=$columnValues[0]->value;

              if (count($columnValues) > 1) {
                  $value = $columnValues[1]->value===''?0:$columnValues[1]->value;                    
              }
              $report['data'][$sectionName]['value']=$value??0;

              if (property_exists($rowItem, 'Rows') 
                          && property_exists($rowItem->Rows, 'Row')) {
                  $rows = $rowItem->Rows->Row;
                  $report['data'][$sectionName]['rows'] = array();

                  foreach ($rows as $row) {
                      $rowValue = $this->extractNameAndValue($row);
                      if ($rowValue) {
                          $report['data'][$sectionName]['rows'][$rowValue['id']] = $rowValue;
                      }
                  }
              }
          }

          return $report;

      } else {
          http_response_code(422);  
          return array(
              "message" => "QBO P&L report is missing Rows->Row array.",
          );
          exit(1);
      }                    

  }

    /**
   * Extract the name and values from a specified row item.
   * The returned object is an array with 'name' and 'value' keys.
   * The value is itself an array.
   * @param mixed $row
   * @return array|false { 'name': string, 'value': array }
   */
  private function extractNameAndValue($row) : array|false {

    $header = false;

    // Handle case where row has multiple sections
    if ($row && property_exists($row, 'Rows')) {
        $header = $row->Header;
        $row = $row->Summary;            
    }

    $rowValues = $row->ColData;
    $name = $rowValues[0]->value;
    if ($header) {
        $id = $header->ColData[0]->id;
    } else {
        $id = $rowValues[0]->id;        
    }
    // remove 'Total ' from start of string
    if (str_starts_with($name, 'Total')){
        $name = substr($name, 6);
    }

    $returnArray = array();
    $returnArray['id'] = $id;   
    $returnArray['name'] = $name;           
    $returnArray['value'] = $rowValues[1]->value===''?0:$rowValues[1]->value;

    return $returnArray;
  }

      /**
     * Given two raw QBO P&L reports, merge them into a single report.
     * @param mixed $current The P&L report for the current period.
     * @param mixed $previous The P&L report for the same period 12 months ago (the 'previous' period).
     * @return array The merged report
     */
    public function mergecurrentAndPreviousPNLReports($current, $previous) : array {

      $return = array();
      $return['title'] = 'Profit & Loss';
      $return['range'] = array();
      $return['range']['currentPeriodStart'] = $current['start'];
      $return['range']['currentPeriodEnd'] = $current['end'];
      $return['range']['previousPeriodStart'] = $previous['start'];
      $return['range']['previousPeriodEnd'] = $previous['end'];

      $currentPeriodData = $current['data'];
      $previousPeriodData = $previous['data'];

      // These are all the possible sections of a QBO P&L report
      $section = array('income', 'cogs', 'grossprofit', 'expenses'
      , 'netoperatingincome', 'otherincome', 'otherexpenses'
      , 'netotherincome', 'netincome');

      // Loop through each section, merging them one by one
      foreach ($section as $sectionName) {

          $sectionItem = new SectionItem;

          // Check if the section exists in the current period data
          if (key_exists($sectionName, $currentPeriodData)) {
              
              $currentPeriodSection = $currentPeriodData[$sectionName];
              $sectionItem->displayName = $currentPeriodSection['name'];
              $sectionItem->currentValue = $currentPeriodSection['value'];

              // Check if the section exists in the previous period data
              if (key_exists($sectionName, $previousPeriodData)) {
                  $previousPeriodSection = $previousPeriodData[$sectionName];
                  $sectionItem->previousValue = $previousPeriodSection['value'];
              } else {
                  $previousPeriodSection = null;
              }                
          } else if (key_exists($sectionName, $previousPeriodData)) {
              $previousPeriodSection = $previousPeriodData[$sectionName];
              $currentPeriodSection = null;
              $sectionItem->displayName = $previousPeriodSection['name'];
              $sectionItem->previousValue = $previousPeriodSection['value'];
              
          } else {
              // no valid sections found for either ccurrent or previous
              // will skip the rest of the foreach loop and return to the top 
              continue;
          }

          // Check if there is a valid 'rows' array on either or both current & previous
          // If found then merge the arrays ... ignoring duplicate keys
          if ($currentPeriodSection && key_exists('rows', $currentPeriodSection) 
                          && count($currentPeriodSection['rows'])) {
              $currentSectionHasRows = true;
              if ($previousPeriodSection && key_exists('rows', $previousPeriodSection) 
                              && count($previousPeriodSection['rows'])) {

                  // The '+' on the next line is the same as array_combine()
                  // This is called the "array union operator"
                  $combined_array = $currentPeriodSection['rows']+$previousPeriodSection['rows'];
                  $previousSectionHasRows = true;
              } else {

                  $combined_array = $currentPeriodSection['rows'];
                  $previousSectionHasRows = false;
              }
          }
          else if ($previousPeriodSection && key_exists('rows', $previousPeriodSection) 
                                  && count($previousPeriodSection['rows'])) {

              $combined_array = $previousPeriodSection['rows'];
              $currentSectionHasRows = false;
              $previousSectionHasRows = true;
          } else {
              $combined_array = [];
              $currentSectionHasRows = false;
              $previousSectionHasRows = false;
          }
          
          
          // loop through the keys of the 'rows' arrayas and merge them
          foreach(array_keys($combined_array) as $keyName) {
              $rowItem = new RowItem;
              if ($currentSectionHasRows && key_exists($keyName, $currentPeriodSection['rows'])) {
                  $rowItem->displayName = $currentPeriodSection['rows'][$keyName]['name'];
                  $rowItem->currentValue = $currentPeriodSection['rows'][$keyName]['value'];
              } else {
                  $rowItem->currentValue = 0;
              }
              if ($previousSectionHasRows && key_exists($keyName, $previousPeriodSection['rows'])) {
                  $rowItem->displayName = $previousPeriodSection['rows'][$keyName]['name'];
                  $rowItem->previousValue = $previousPeriodSection['rows'][$keyName]['value'];
              } else {
                  $rowItem->previousValue = 0;
              }

              // Add the rowItem to the array if either value is non-sero
              if($rowItem->currentValue || $rowItem->previousValue) {
                  array_push($sectionItem->rows, $rowItem);
              }
          }

          $return[$sectionName] = $sectionItem;
          
      }

      return $return;
  }
}