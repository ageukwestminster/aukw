<?php

namespace Models;

use QuickBooksOnline\API\ReportService\ReportService;
use QuickBooksOnline\API\ReportService\ReportName;

use DateTime;

class QuickbooksReport{

    public $startdate;
    public $enddate;
    public $groupBy;
    public $summarizeColumn;
    public $item;

    public function profitAndLoss(){

        $auth = new QuickbooksAuth();
        $dataService = $auth->prepare();
        if ($dataService == false) {
          return;
        }
        try{
            $serviceContext = $auth->getServiceContext();
        }
        catch (\Exception $e) {
            http_response_code(400);  
            echo json_encode(
              array("message" => "Unable to proceed with QB callback: 'state' does not match initial value.")
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
  
        $reportService->setStartDate($this->startdate);
        $reportService->setEndDate($this->enddate);
        $reportService->setSummarizeColumnBy($this->summarizeColumn);

        $profitAndLossReport = $reportService->executeReport(ReportName::PROFITANDLOSS);

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

    public function itemSales(){

        $auth = new QuickbooksAuth();
        $dataService = $auth->prepare();
        if ($dataService == false) {
          return;
        }
        $serviceContext = $auth->getServiceContext();
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

        if ($customerSales && property_exists($customerSales, 'Rows') 
                            && property_exists($customerSales->Rows, 'Row')) {
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