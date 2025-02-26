<?php

namespace Models;

use \QuickBooksOnline\API\ReportService\ReportName;

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
            
    /** @disregard Intelephense error on next line */
    $data = $report->Rows->Row[0]->Rows->Row;

    // Handle case where report has multiple sections
    if ($data[0] && property_exists($data[0], 'Rows')) {
        $data = $data[0]->Rows->Row;
    }

    //Convert the report object to something readable
    foreach ($data as $value) {
        $line=array();
        
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

        $employee=array();
        $employee['value'] = $value->ColData[3]->value;
        if (
            isset($value->ColData[3]->id) && 
            trim($value->ColData[3]->id) != ''
        ) {
            $employee['id'] = $value->ColData[3]->id; 
        } else {
            $employee['id'] = null;
        }
        $line['emp_name'] = $employee;

        $line['memo'] = $value->ColData[4]->value;

        $account=array();
        $account['value'] = $value->ColData[5]->value;
        if (
            isset($value->ColData[5]->id) && 
            trim($value->ColData[5]->id) != ''
        ) {
            $account['id'] = $value->ColData[5]->id; 
        } else {
            $account['id'] = null;
        }
        $line['account'] = $account;

        $line['is_cleared'] = $value->ColData[6]->value;
        $line['amount'] = $value->ColData[7]->value;
        $line['balance'] = $value->ColData[8]->value;
        array_push($report_arr, $line);
    }

    if ($this->sortAscending) {
        return $report_arr;    
    } else {           
        return array_reverse($report_arr);
    }
  }
}