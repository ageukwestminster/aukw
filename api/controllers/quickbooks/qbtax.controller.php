<?php

namespace Controllers;

use \Models\QuickbooksQuery;
use Core\QuickbooksConstants as QBO;

/**
 * Controller to accomplish QBO Transfer related tasks. 
 *
 * @category  Controller
*/
class QBTaxCtl{

  /**
   * Return details of the QBO Transfer identified by $id
   *
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function read_all(string $realmid){  
    try {
      $taxCodes = QuickbooksQuery::getInstance()
        ->setRealmID($realmid)  
        ->list_tax_codes();

      echo json_encode($taxCodes, JSON_NUMERIC_CHECK);
    } catch (\Throwable $e) {
      http_response_code(400);  
      echo json_encode(
          array(
              "message" => "Unable to retrieve QB Tax Codes.",
              "extra" => $e->getMessage()
              )
      );
      exit(1);
    }
  }

    /**
   * Return details of the QBO Transfer identified by $id
   *
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function read_one(string $realmid, string $id){  
    try {
      $taxCodes = QuickbooksQuery::getInstance()
        ->setRealmID($realmid)  
        ->list_tax_codes($id);

      echo json_encode($taxCodes, JSON_NUMERIC_CHECK);
    } catch (\Throwable $e) {
      http_response_code(400);  
      echo json_encode(
          array(
              "message" => "Unable to retrieve QB Tax Codes.",
              "extra" => $e->getMessage()
              )
      );
      exit(1);
    }
  }

  
}