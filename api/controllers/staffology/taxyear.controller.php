<?php

namespace Controllers;

use Exception;
use Core\ErrorResponse as Error;

/**
 * Controller to accomplish PayRun related tasks. 
 *
 * @category  Controller
*/
class TaxYearCtl{

  /**
   * Return names of all Tax Years, in JSON format
   *
   * @return void Output is echo'd directly to response 
   */
  public static function read_names():void{  
    try {
      
      $taxyears = array();

      $currentYear = (int)date("Y");
      $currentMonth = (int)date("m");    

      // Adjust current year for tax year starting in April
      $currentYear += ($currentMonth < 4) ? -1 : 0;

      for ($i = 2024; $i <= $currentYear; $i++) {
        array_push($taxyears, "Year" . $i);
      }

      echo json_encode($taxyears, JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Error retrieving names of Tax Years.", $e);
    }
  }





}