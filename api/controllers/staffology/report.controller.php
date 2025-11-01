<?php

namespace Controllers\Staffology;

use Core\ErrorResponse as Error;
use Exception;
use Models\Staffology\GrossToNetReport;
use \Models\Staffology\ParseGrosstoNetReport;

/**
 * Controller to accomplish PayRun related tasks. 
 *
 * @category  Controller
*/
class PayrollReportCtl{

  /**
   * Return details of all PayRuns, in JSON format
   *
   * @param string $employerId The Staffology Employer ID
   * @param string $taxYear The Staffology Tax Year
   * @param int $month The month number (1-12)
   * @return void Output is echo'd directly to response 
   */
  public static function gross_to_net(string $employerId, string $taxYear, int $month):void{  
    try {

      $payrollDate = sprintf('%04d-%02d-25', intval(substr($taxYear, 4)), $month);

      $salaryData = GrossToNetReport::getInstance()
        ->setEmployerId($employerId)
        ->setTaxYear($taxYear)
        ->setFromPeriod($month)
        ->setToPeriod($month)
        ->setSortDescending(false)
        ->generate();
        
      $payslips = ParseGrosstoNetReport::parse($salaryData, $payrollDate);

      echo json_encode($payslips, JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Error retrieving details of all Rules.", $e);
    }
  }



}