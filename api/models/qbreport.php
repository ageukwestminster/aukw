<?php

namespace Models;

use QuickBooksOnline\API\ReportService\ReportService;
use QuickBooksOnline\API\ReportService\ReportName;

use DateTime;

class QuickbooksReport{

    public $start;
    public $end;
    public $sortbycolumn;

    public function profitAndLoss(){

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
  
        $reportService->setStartDate($this->start);
        $reportService->setEndDate($this->start);

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

}