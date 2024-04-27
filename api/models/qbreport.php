<?php

namespace Models;

use QuickBooksOnline\API\ReportService\ReportService;
use QuickBooksOnline\API\ReportService\ReportName;

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
            echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
            echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
            echo "The Response message is: " . $error->getResponseBody() . "\n";
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
  
        $reportService
            ->setStartDate($this->startdate)
            ->setEndDate($this->enddate)
            ->setSummarizeColumnBy($this->summarizeColumn);

        if ($this->account) {
            $reportService->setAccount($this->account);
        }

        if ($this->columns) {
            $reportService->setColumns($this->columns);
        }

        $report = $reportService->executeReport(ReportName::GENERALLEDGER);

        $error = $dataService->getLastError();
        if ($error) {
            echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
            echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
            echo "The Response message is: " . $error->getResponseBody() . "\n";
            return [];
        }
        else {
            
            $report_arr=array();
            /** @disregard Intelephense error on next line */
            $data = $report->Rows->Row[0]->Rows->Row;

            foreach ($data as $value) {
                $line=array();

                
                $line['date'] = $value->ColData[0]->value;

                $txn=array();
                $txn['value'] = $value->ColData[1]->value;
                if (isset($value->ColData[1]->id)) $txn['id'] = $value->ColData[1]->id;                
                $line['type'] = $txn;

                $line['docnumber'] = $value->ColData[2]->value;

                $employee=array();
                $employee['value'] = $value->ColData[3]->value;
                if (isset($value->ColData[3]->id)) $employee['id'] = $value->ColData[3]->id;
                $line['emp_name'] = $employee;

                $line['memo'] = $value->ColData[4]->value;

                $account=array();
                $account['value'] = $value->ColData[5]->value;
                if (isset($value->ColData[5]->id)) $account['id'] = $value->ColData[5]->id; 
                $line['account'] = $account;

                $line['amount'] = $value->ColData[6]->value;
                $line['balance'] = $value->ColData[7]->value;
                array_push($report_arr, $line);
            }

            return $report_arr;
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
  
        $reportService->setStartDate($this->startdate);
        $reportService->setEndDate($this->enddate);
        $reportService->setItem($this->item);

        $customerSales = $reportService->executeReport(ReportName::ITEMSALES);

        $error = $dataService->getLastError();
        if ($error) {
            echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
            echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
            echo "The Response message is: " . $error->getResponseBody() . "\n";
            return;
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
    
}