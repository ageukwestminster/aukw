<?php

namespace Controllers;

use \Core\ErrorResponse as Error;
use \Models\Rules;
use Exception;
/**
 * Controller to accomplish trading Rule based tasks. 
 *
 * @category  Controller
*/
class RuleCtl{


  /**
   * Return details of all Rules
   * 
   * @return void Output is echo'd directly to response 
   */
  public static function read_all():void{  
    try {
      $rules = RuleCtl::read_all_raw();

      echo json_encode($rules, JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Error retrieving details of all Rules.", $e);
    }
  }

  /**
   * Return details of all Rules
   * 
   * @return array
   */
  protected static function read_all_raw():array{  
    try {
      return Rules::getInstance()
        ->read();

    } catch (Exception $e) {
      Error::response("Error retrieving details of all Rules.", $e);
    }
  }

  /**
   * 
   * 
   * @return void Output is echo'd directly to response.
   */
  protected static function interco_trade_from_rules():void{  
    try {
      $rules = Rules::getInstance()
        ->read();


      

    } catch (Exception $e) {
      Error::response("Error retrieving details of the new interco trade.", $e);
    }
  }


}