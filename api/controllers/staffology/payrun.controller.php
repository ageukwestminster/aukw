<?php

namespace Controllers;

use Core\ErrorResponse as Error;
use Exception;
use Models\Staffology\PayRuns;

/**
 * Controller to accomplish PayRun related tasks. 
 *
 * @category  Controller
*/
class PayRunCtl{

  /**
   * Return details of all PayRuns, in JSON format
   *
   * @param string $employerId The Staffology Employer ID
   * @return void Output is echo'd directly to response 
   */
  public static function read_all(string $employerId, string $taxYear):void{  
    try {
      $payruns = PayRunCtl::read_all_raw($employerId, $taxYear);

      echo json_encode($payruns, JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Error retrieving details of all Rules.", $e);
    }
  }


  /**
   * Return details of all PayRuns, as returnd from the API
   * 
   * @param string $employerId The Staffology Employer ID
   * @return void Output is echo'd directly to response 
   */
  public static function read_all_raw(string $employerId, string $taxYear){  
    try {
      return PayRuns::getInstance()
        ->setEmployerId($employerId)
        ->setTaxYear($taxYear)
        ->read();
    } catch (Exception $e) {
      Error::response("Error retrieving details of Staffology PayRuns.", $e);
    }
  }



}