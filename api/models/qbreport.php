<?php

namespace Models;

use QuickBooksOnline\API\ReportService\ReportService;
use QuickBooksOnline\API\ReportService\ReportName;
use QuickBooksOnline\API\Exception\SdkException;

use DateTime;

/**
 * QBO Wrapper class that allows us to run seelcted QBO reports.
 * 
 * @category Model
 */
class QuickbooksReport{

    /**
     * The start date of the report period.
     *
     * @var string
     */
    public string $startdate;
    /**
     * The end date of the report period.
     *
     * @var string
     */
    public string $enddate;
    /**
     * Summarize P&L report by this column
     *
     * @var string
     */
    public string $summarizeColumn;
    /**
     * Only calculate item sales for this item. Use 'null' for all items.
     *
     * @var int|null
     */
    public int|null $item;
    /**
     * The QBO company ID
     *
     * @var string
     */
    public string $realmid;
    /**
     * A QBO account ID
     *
     * @var int
     */
    public int $account = 0;
    /**
     * A QBO column list
     *
     * @var string
     */
    public string $columns = '';
    /**
     * The QBO column to sort by. Usu 'tx_date'.
     *
     * @var string
     */
    public string $sortBy = '';
    /**
     * 'True' if should be sorted by date ascending
     *
     * @var bool
     */
    public bool $sortAscending = true;

        /**
     * Generate a Profit & Loss report
     *
     * @return array
     * 
     */
    public function profitAndLoss(){

        $auth = new QuickbooksAuth();
        $dataService = $auth->prepare($this->realmid);
        if ($dataService == false) {
          return;
        }
        try{
            $serviceContext = $auth->getServiceContext($this->realmid);
        }
        catch (\Exception $e) {
            http_response_code(400);  
            echo json_encode(
              array("message" => "Unable to proceed with QB report.",
                "details" => $e->getMessage())
            );
            exit(0);            
        }
        if ($serviceContext == false) {
            return;
        }
        $reportService = new ReportService($serviceContext);
        if ($reportService == false) {
            return;
        }
  
        $profitAndLossReport = $reportService
            ->setStartDate($this->startdate)
            ->setEndDate($this->enddate)
            ->setSummarizeColumnBy($this->summarizeColumn)
            ->setColumns($this->columns)
            ->executeReport(ReportName::PROFITANDLOSS);

        $error = $dataService->getLastError();
        if ($error) {
            throw new SdkException("The Response message is: " . $error->getResponseBody());
        }
        else {
            return $profitAndLossReport;
        }

    }

    /**
     * Generate a General Ledger report
     *
     * @return array
     * 
     */
    public function general_ledger(){

        $auth = new QuickbooksAuth();
        $dataService = $auth->prepare($this->realmid);
        if ($dataService == false) {
          return;
        }
        try{
            $serviceContext = $auth->getServiceContext($this->realmid);
        }
        catch (\Exception $e) {
            http_response_code(400);  
            echo json_encode(
              array("message" => "Unable to proceed with QB report.",
                "details" => $e->getMessage())
            );
            exit(0);            
        }
        if ($serviceContext == false) {
            return;
        }
        $reportService = new ReportService($serviceContext);
        if ($reportService == false) {
            return;
        }
  
        $report = $reportService
            ->setStartDate($this->startdate)
            ->setEndDate($this->enddate)
            ->setSummarizeColumnBy($this->summarizeColumn)
            ->setAccount($this->account)
            ->setColumns($this->columns)
            ->setSortBy($this->sortBy)
            ->setSortOrder('ascend') //'descend' corrupts the running balance figures
            ->executeReport(ReportName::GENERALLEDGER);

        $error = $dataService->getLastError();
        if ($error) {
            throw new SdkException("The Response message is: " . $error->getResponseBody());
        }
        else {                        
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

    /**
     * Generate a listing of sales by Item (aka Product)
     *
     * @return array
     * 
     */
    public function itemSales(){

        $auth = new QuickbooksAuth();
        $dataService = $auth->prepare($this->realmid);
        if ($dataService == false) {
          return;
        }
        $serviceContext = $auth->getServiceContext($this->realmid);
        if ($serviceContext == false) {
            return;
        }
        $reportService = new ReportService($serviceContext);
        if ($reportService == false) {
            return;
        }

        $customerSales = $reportService
            ->setStartDate($this->startdate)
            ->setEndDate($this->enddate)
            ->setSummarizeColumnBy($this->summarizeColumn)
            ->setItem($this->item)
            ->executeReport(ReportName::ITEMSALES);

        $error = $dataService->getLastError();
        if ($error) {
            throw new SdkException("The Response message is: " . $error->getResponseBody());
        }        

        $returnObj = array();

        /** @disregard Intelephense error on next line */
        if ($customerSales && property_exists($customerSales, 'Rows') 
                            && property_exists($customerSales->Rows, 'Row')) {
            /** @disregard Intelephense error on next line */
            $dataArray = $customerSales->Rows->Row;

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
     * Convert the raw QuickBooks Profit and Loss report into a format that can we used
     * to report the QMA information.
     * 
     * We wish to report numbers for the latest time period and the time period one
     * year before that. Because of a limitation in the QBO api, we receive data for
     * all intervenign periods between the two relevant periods. We must ignore these 
     * periods.
     * 
     * @param array $profitAndLossReport A report array received from QuickBooks
     */
    public function adaptProfitAndLossToQMA($profitAndLossReport) {

        $report=array();            

        /** @disregard Intelephense error on next line */
        if ($profitAndLossReport && property_exists($profitAndLossReport, 'Columns')
                 && property_exists($profitAndLossReport->Columns, 'Column')) {

            /** @disregard Intelephense error on next line */
            $columns = $profitAndLossReport->Columns->Column;

            // Find the column numbers for the two periods that we wish to report back to user
            $numberOfColumns = count($columns); // Total number of columns
            $twelveMonthsAgoPeriodColumnNumber = 1; // ignore the first column ( which is 'Account')
            $latestPeriodColumnNumber = $numberOfColumns-2; // The latest period data is in the second last column
            $indices = array($twelveMonthsAgoPeriodColumnNumber, $latestPeriodColumnNumber); 

            $report['period'] = array();
            array_push($report['period'], $columns[$twelveMonthsAgoPeriodColumnNumber]->ColTitle);
            array_push($report['period'], $columns[$latestPeriodColumnNumber]->ColTitle);

            /** @disregard Intelephense error on next line */
            $dataArray = $profitAndLossReport->Rows->Row; // This is an array of data objects

            foreach ($dataArray as $rowItem) {

                $summaryItem = $rowItem->Summary;
                $sectionName = strtolower($rowItem->group);
                $report[$sectionName] = array();
                $report[$sectionName]['total'] = array();

                $columnValues = $summaryItem->ColData;
                array_push($report[$sectionName]['total'], 
                    $columnValues[$twelveMonthsAgoPeriodColumnNumber]->value);
                array_push($report[$sectionName]['total'], 
                    $columnValues[$latestPeriodColumnNumber]->value);

                // Interrogate components of individual sections
                switch ($sectionName) {
                    case 'income':                  
                        $rows = $rowItem->Rows->Row;
                        $report[$sectionName]['rows'] = array();

                        foreach ($rows as $row) {
                            $rowValue = QuickbooksReport::extractNameAndValue($row, 
                                $twelveMonthsAgoPeriodColumnNumber, 
                                $latestPeriodColumnNumber);
                            if ($rowValue) {
                                array_push($report[$sectionName]['rows'], $rowValue);
                                
                                if ($rowValue['name'] == 'Ragging') {
                                    $report['ragging'] = array();
                                    $report['ragging']['total'] = $rowValue['value'];
                                }
                            }
                        }
                        break;

                    case 'expenses':
                        $rows = $rowItem->Rows->Row;
                        $report[$sectionName]['rows'] = array();

                        foreach ($rows as $row) {
                            $rowValue = QuickbooksReport::extractNameAndValue($row, 
                                $twelveMonthsAgoPeriodColumnNumber, 
                                $latestPeriodColumnNumber);
                            if ($rowValue)
                                array_push($report[$sectionName]['rows'], $rowValue);                        
                        }
                        break;

                    case 'otherincome':
                        $rows = $rowItem->Rows->Row;
                        $report[$sectionName]['rows'] = array();
                        foreach ($rows as $row) {
                            $rowValue = QuickbooksReport::extractNameAndValue($row, 
                                $twelveMonthsAgoPeriodColumnNumber, 
                                $latestPeriodColumnNumber);
                            if ($rowValue) {
                                array_push($report[$sectionName]['rows'], $rowValue); 
                            
                                if (str_starts_with($rowValue['name'], 'Donation')) {
                                    $report['donations'] = array();
                                    $report['donations']['total'] = $rowValue['value'];
                                }
                            }
                        }
                        break;
                    case 'otherexpenses':
                        $rows = $rowItem->Rows->Row;
                        $report[$sectionName]['rows'] = array();
                        foreach ($rows as $row) {
                            $rowValue = QuickbooksReport::extractNameAndValue($row, 
                                $twelveMonthsAgoPeriodColumnNumber, 
                                $latestPeriodColumnNumber);
                            if ($rowValue)
                                array_push($report[$sectionName]['rows'], $rowValue); 
                        }
                        break;
                }
            }

            return $report;

        } else {
            http_response_code(422);  
            return array(
                "message" => "QB result set is missing Columns array.",
            );
        }
        
                 

    }

    /**
     * Extract the name and values from a specified row item.
     * The returned object is an array with 'name' and 'value' keys.
     * The value is itself an array.
     * @param mixed $row
     * @param int $twelveMonthsAgoPeriodColumnNumber
     * @param int $latestPeriodColumnNumber
     * @return array|false { 'name': string, 'value': array }
     */
    private function extractNameAndValue($row, 
        int $twelveMonthsAgoPeriodColumnNumber, 
        int $latestPeriodColumnNumber) : array|false {

        $nonZeroValue = false;

        // Handle case where row has multiple sections
        if ($row && property_exists($row, 'Rows')) {
            $row = $row->Summary;
        }

        $rowValues = $row->ColData;
        $name = $rowValues[0]->value;

        $returnArray = array();      

        // remove 'Total ' from start of string
        if (str_starts_with($name, 'Total')){
            $returnArray['name'] = substr($name, 6);
        } else {
            $returnArray['name'] = $name;
        }
        
        $returnArray['value'] = array();

        $twelveMthsAgoValue = empty($rowValues[$twelveMonthsAgoPeriodColumnNumber]->value)?
                                0:
                                $rowValues[$twelveMonthsAgoPeriodColumnNumber]->value;
        array_push($returnArray['value'], $twelveMthsAgoValue);
        if ($twelveMthsAgoValue != 0) {
            $nonZeroValue = true;  
        }

        $twelveMthsAgoValue = empty($rowValues[$latestPeriodColumnNumber]->value)?
                                    0:
                                    $rowValues[$latestPeriodColumnNumber]->value;
        array_push($returnArray['value'], $twelveMthsAgoValue);
        if ($twelveMthsAgoValue != 0) {
            $nonZeroValue = true;  
        }

        // Only return the values if one of the values is nonZero
        return $nonZeroValue?$returnArray:false;
    }
    
}