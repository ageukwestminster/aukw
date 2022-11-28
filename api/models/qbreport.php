<?php

namespace Models;

use QuickBooksOnline\API\ReportService\ReportService;
use QuickBooksOnline\API\ReportService\ReportName;

use DateTime;

class QuickbooksReport{

    public $startdate;
    public $enddate;
    public $summarizeColumn;

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

}