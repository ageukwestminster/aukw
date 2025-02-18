<?php

namespace Models;

use QuickBooksOnline\API\ReportService\ReportService;
use QuickBooksOnline\API\ReportService\ReportName;
use QuickBooksOnline\API\Exception\SdkException;

use DateTime;

/**
 * QBO Wrapper class that allows us to run selected QBO reports.
 * 
 * A QBO Report is a complex object of the form:
 * 
 * {
 *   "Header": {...},      // Basic attributes of the report such as Start & end dates
 *   "Columns": {...},     // Names and types of columns
 *   "Rows": {...},        // One or more Row objects containing the report values
 * }
 * 
 * @category Model
 */
class QuickbooksReport{

    private $dataService;
    private $reportService;

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
     * Predefined date range
     *
     * @var string
     */
    public string $dateMacro;
    /**
     * Group the P&L amounts by this column
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

        $this->dataService = $auth->prepare($this->realmid);
        if ($this->dataService == false) {
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
        $this->reportService = new ReportService($serviceContext);
        if ($this->reportService == false) {
            return;
        }
  
        $profitAndLossReport = $this->reportService
            ->setStartDate($this->startdate??'')
            ->setEndDate($this->enddate??'')
            ->setSummarizeColumnBy($this->summarizeColumn??'')
            ->setDateMacro($this->dateMacro??'')
            ->setColumns($this->columns)
            //->setSortBy($this->sortBy) // Not valid for PNL report. Use PNLDetail if needed.
            ->executeReport(ReportName::PROFITANDLOSS);

        $error = $this->dataService->getLastError();
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
     * all intervening periods between the two relevant periods. We must ignore these 
     * periods.
     * 
     * @param array $profitAndLossReport A report array received from QuickBooks
     */
    public function summarisePNLFromQBO($pnlReport, $start, $end) {

        $report=array();            
        $report['start'] = $start;
        $report['end'] = $end;
        $report['data'] = array();

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
                $report['data'][$sectionName]['name']= $columnValues[0]->value;
                $report['data'][$sectionName]['value']= $columnValues[1]->value;

                if (property_exists($rowItem, 'Rows') 
                            && property_exists($rowItem->Rows, 'Row')) {
                    $rows = $rowItem->Rows->Row;
                    $report['data'][$sectionName]['rows'] = array();

                    foreach ($rows as $row) {
                        $rowValue = QuickbooksReport::extractNameAndValue($row);
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
                "message" => "QB report is missing Rows->Row array.",
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
        $returnArray['value'] = $rowValues[1]->value;

        return $returnArray;
    }
    
    public function mergecurrentAndPreviousPNLReports($current, $previous) {

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
            if (key_exists('rows', $currentPeriodSection) 
                            && count($currentPeriodSection['rows'])) {
                if (key_exists('rows', $previousPeriodSection) 
                                && count($previousPeriodSection['rows'])) {

                    // The '+' on th enext line is the same as array_combine()
                    // This is called the "array union operator"
                    $combined_array = $currentPeriodSection['rows']+$previousPeriodSection['rows'];

                } else {

                    $combined_array = $currentPeriodSection['rows'];

                }
            }
            else if (key_exists('rows', $previousPeriodSection) 
                                    && count($previousPeriodSection['rows'])) {

                $combined_array = $previousPeriodSection['rows'];

            } else {
                $combined_array = [];
            }
            
            
            // loop through the keys of the 'rows' arrayas and merge them
            foreach(array_keys($combined_array) as $keyName) {
                $rowItem = new RowItem;
                if (key_exists($keyName, $currentPeriodSection['rows'])) {
                    $rowItem->displayName = $currentPeriodSection['rows'][$keyName]['name'];
                    $rowItem->currentValue = $currentPeriodSection['rows'][$keyName]['value'];
                } else {
                    $rowItem->currentValue = 0;
                }
                if (key_exists($keyName, $previousPeriodSection['rows'])) {
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

class RowItem{ 
    public string $displayName ='';
    public float $currentValue = 0;
    public float $previousValue = 0;
}
class SectionItem extends RowItem{ 
    public array $rows = [];
}