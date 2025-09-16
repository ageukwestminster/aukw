<?php

namespace Models;

use \QuickBooksOnline\API\ReportService\ReportName;
use Core\QuickbooksConstants as QBO;

class QBGeneralLedgerReport extends QuickbooksReport{
  
  /** Execute the General Ledger report in QBO */
  function queryQuickBooks() : mixed {

    $this->report = $this->reportService
    ->setStartDate($this->startdate)
    ->setEndDate($this->enddate)
    ->setSummarizeColumnBy($this->summarizeColumn)
    ->setAccount($this->account)
    ->setColumns($this->columns)
    ->setSortBy($this->sortBy)
    ->setSortOrder('ascend') //'descend' corrupts the running balance figures
    ->executeReport(ReportName::GENERALLEDGER);

    return $this->report;
  }

    /**
     * Convert the General Ledger QBO report to something more useful
     *
     * @return array
     * 
     */
  public function adaptReport() : array {

    $report = $this->report;

    $report_arr=array();      

    // Is the report empty? If so, return an empty record set.
    if ($report->Header && $report->Header->Option 
            && is_array($report->Header->Option) && count($report->Header->Option)
            && $report->Header->Option[0]->Name == 'NoReportData'
            && $report->Header->Option[0]->Value == 'true') {
        return [];
    }
            
    /** @disregard Intelephense error on next line */
    $data = $report->Rows->Row[0]->Rows->Row;

    // Handle case where report has multiple sections
    if ($data[0] && property_exists($data[0], 'Rows')) {
        $data = $data[0]->Rows->Row;
    }

    $i=0;

    //Convert the report object to something readable
    foreach ($data as $value) {
        $line=array();
        
        $line['id'] = $i++;

        $line['date'] = $value->ColData[0]->value;

        $txn=array();
        $txn['value'] = $value->ColData[1]->value;
        if (
            isset($value->ColData[1]->id) && 
            trim($value->ColData[1]->id) != ''
        ) {
            $txn['id'] = $value->ColData[1]->id; 
        } else {
            $txn['id'] = null;
        }             
        $line['type'] = $txn;

        $line['docnumber'] = $value->ColData[2]->value;
        
        $line['name'] = $this->extractIdValuePairFromColData($value->ColData[3]);

        $line['employee'] = $this->extractIdValuePairFromColData($value->ColData[4]);

        $line['memo'] = $value->ColData[5]->value;

        $line['account'] = $this->extractIdValuePairFromColData($value->ColData[6]);

        $line['is_cleared'] = $value->ColData[7]->value;
        $line['amount'] = $value->ColData[8]->value;
        $line['balance'] = $value->ColData[9]->value;


        array_push($report_arr, $line);
    }

    if ($this->sortAscending) {
        return $report_arr;    
    } else {           
        return array_reverse($report_arr);
    }
  }

  private function extractIdValuePairFromColData($dataValue):array {
    $returnValue=array();
    $returnValue['value'] = $dataValue->value;
    if (
        isset($dataValue->id) && 
        trim($dataValue->id) != ''
    ) {
        $returnValue['id'] = $dataValue->id; 
    } else {
        $returnValue['id'] = null;
    }
    return $returnValue;
  }
}