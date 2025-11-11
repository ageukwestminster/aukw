<?php

namespace Controllers\Staffology;

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
      echo json_encode(TaxYearCtl::read_names_as_array(), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Error retrieving names of Tax Years.", $e);
    }
  }

    /**
   * Return names of all Tax Years, in JSON format
   *
   * @return void Output is echo'd directly to response 
   */
  public static function read_names_as_array():array{  
    try {
      
      $taxyears = array();

      $currentYear = (int)date("Y");
      $currentMonth = (int)date("m");    

      // Adjust current year for tax year starting in April
      $currentYear += ($currentMonth < 4) ? -1 : 0;

      for ($i = 2024; $i <= $currentYear; $i++) {
        array_push($taxyears, [
          "name" => $i . "/" . substr($i+1, 2),
          "value" => "Year" . $i,
          "year" => $i
                  ]);
      }

      return $taxyears;
    } catch (Exception $e) {
      Error::response("Error retrieving names of Tax Years.", $e);
    }
  }

  /**
   * Return names of the current Tax Year, in JSON format
   *
   * @return void Output is echo'd directly to response 
   */
  public static function read_name_latest():void{  
    try {
      
      $taxYears = TaxYearCtl::read_names_as_array();
      if ($taxYears === null || count($taxYears) === 0) {
        throw new Exception("No tax years found.");
      } else {
        // Sort descending by year to get the latest first
        usort($taxYears, function($a, $b) {
          return $b['year'] <=> $a['year'];
        });
      }
      echo json_encode($taxYears[0], JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Error retrieving names of Tax Years.", $e);
    }    
  }





}