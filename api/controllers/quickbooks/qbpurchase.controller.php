<?php

namespace Controllers;

use \Models\QuickbooksPurchase;
use Core\QuickbooksConstants as QBO;

/**
 * Controller to accomplish QBO Bill (or expense) related tasks. 
 *
 * @category  Controller
*/
class QBPurchaseCtl{

  /**
   * Return details of the QBO Purchase identified by $id
   *
   * @param string $realmid The company ID for the QBO company.
   * @param string $id
   * @return void Output is echo'd directly to response 
   */
  public static function read_one(string $realmid, string $id){  
    try {
    $model = QuickbooksPurchase::getInstance()
      ->setRealmID($realmid)  
      ->setId($id);

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
    } catch (\Exception $e) {
      http_response_code(400);  
      echo json_encode(
          array(
              "message" => "Unable to retrieve QB Purchase with id=$id".". ",
              "extra" => $e->getMessage()
              )
      );
      exit(1);
    }
  }

  /**
   * Delete from QBO the purchase identified by $id
   *
   * On success the PHP call exits with HTTP status 200 and a message confirming success.
   * If this fails the PHP call exits with HTTP status 400 and a message describing the error.
   * @param string $realmid The company ID for the QBO company.
   * @param int $id The QBO id, not the DocNumber
   * @return void Output is echoed directly to response 
   */
  public static function delete(string $realmid, int $id){  

    try {

      $model = QuickbooksPurchase::getInstance()
        ->setId($id)
        ->setRealmID($realmid); 

      if($model->delete()) {
        echo json_encode(
          array(
            "message" => "Purchase with id=$id was deleted.",
            "id" => $id)
            , JSON_NUMERIC_CHECK);
      } 
    
    } catch (\Exception $e) {
      http_response_code(400);  
      echo json_encode(
        array(
          "message" => "Unable to delete QB Purchase with id=$id".". ",
          "extra" => $e->getMessage()
        )
      );
    }
  }  

  /**
   * Create a QBO purchase from data supplied via http POST
   *
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echoed directly to response 
   * 
   */
  public static function create(string $realmid){  
    
    try{

      $data = json_decode(file_get_contents("php://input"));

      if (!isset($data->date)) {
        throw new \InvalidArgumentException("'date' property is missing from POST body.");
      } else if (!\Core\DatesHelper::validateDate($data->date) ) {
        throw new \InvalidArgumentException("'date' property is not in the correct format. Value provided: $data->date, expect yyyy-mm-dd format.");
      } else if (!isset($data->bankaccountno)) {
        throw new \InvalidArgumentException("'bankaccountno' property is missing from POST body.");
      } else if (!isset($data->expenseaccountno)) {
        throw new \InvalidArgumentException("'expenseaccountno' property is missing from POST body.");
      } else if ($data->expenseaccountno == $data->bankaccountno) {
        throw new \InvalidArgumentException("'expenseaccountno' must be different from 'bankaccountno'.");
      } else if (!isset($data->amount)) {
        throw new \InvalidArgumentException("'amount' property is missing from POST body.");
      } else if ($data->amount <= 0) {
        throw new \InvalidArgumentException("'amount' property must be greater than zero.");
      } else if ($data->taxamount < 0) {
        throw new \InvalidArgumentException("'taxamount' property must be greater than or equal to zero.");
      } 
      if (!isset($data->entityno)) {
        throw new \InvalidArgumentException("'entityno' property is missing from POST body.");
      } 

      
      if ($data->taxamount == 0 ) {
        $taxcode = QBO::$zero_rated_taxcode;
        $taxRateRef = QBO::$zero_rated_purchases_taxrate;
      } else {
        $taxcode = QBO::$standard_rated_taxcode;
        $taxRateRef = QBO::$standard_rated_purchases_taxrate;
      }

      $result = QuickbooksPurchase::getInstance()
        ->setRealmID($realmid)
        ->setTxnDate($data->date)
        ->setEntityNo($data->entityno)
        ->setBankAccountNo($data->bankaccountno)
        ->setExpenseAccountNo($data->expenseaccountno)
        ->setPrivateNote(isset($data->note)?$data->note:'')
        ->setDescription(isset($data->description)?$data->description:'')
        ->setDocnumber(isset($data->docnumber)?$data->docnumber:'')
        ->setAmount($data->amount)
        ->setTaxAmount($data->taxamount)
        ->setTaxCode($taxcode)
        ->setTaxRate($taxRateRef)
        ->create();

      if ($result) {
          echo json_encode(
              array("message" => "Purchase has been added for " . $data->date . ".",
                  "id" => $result->Id)
            );
      }

    } catch (\Exception $e) {
      http_response_code(400);  
      echo json_encode(
        array(
          "message" => "Unable to create Purchase in Quickbooks. ",
          "extra" => $e->getMessage()
          )
          , JSON_NUMERIC_CHECK);
      exit(1);
    }    
  }  
          
}