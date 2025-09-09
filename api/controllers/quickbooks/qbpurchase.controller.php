<?php

namespace Controllers;

use \Models\QuickbooksPurchase;
use Core\QuickbooksConstants as QBO;
use \Core\ErrorResponse as Error;
use Exception;

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
      Error::response("Unable to retrieve QB Purchase with id=$id.", $e);
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
      Error::response("Unable to delete QB Purchase with id=$id.", $e);
    }
  }  

  /**
   * Create a QBO purchase from data supplied via http POST. This is a single line
   * Purchase, not a multi-line Bill. The 'amount' provided is the total amount including tax.
   *
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echoed directly to response 
   * 
   */
  public static function create(string $realmid){  
    
    try{

      $data = json_decode(file_get_contents("php://input"));

      if (!isset($data->txnDate)) {
        throw new \InvalidArgumentException("'txnDate' property is missing from POST body.");
      } else if (!\Core\DatesHelper::validateDate($data->txnDate) ) {
        throw new \InvalidArgumentException("'txnDate' property is not in the correct format. Value provided: $data->date, expect yyyy-mm-dd format.");
      } else if (!isset($data->bankAccount)) {
        throw new \InvalidArgumentException("'bankAccount' property is missing from POST body.");
      } else if (!isset($data->account)) {
        throw new \InvalidArgumentException("'account' property is missing from POST body.");
      } else if ($data->account == $data->bankAccount) {
        throw new \InvalidArgumentException("'expenseAccount' must be different from 'bankAccount'.");
      } else if (!isset($data->amount)) {
        throw new \InvalidArgumentException("'amount' property is missing from POST body.");
      } else if ($data->amount <= 0) {
        throw new \InvalidArgumentException("'amount' property must be greater than zero.");
      } else if (is_null($data->taxAmount) || $data->taxAmount < 0) {
        throw new \InvalidArgumentException("'taxAmount' property must exist and be greater than or equal to zero.");
      } 
      if (!isset($data->entity)) {
        throw new \InvalidArgumentException("'entity' property is missing from POST body.");
      } 

      
      if ($data->taxAmount == 0 ) {
        $taxcode = QBO::$zero_rated_taxcode;
        $taxRateRef = QBO::$zero_rated_purchases_taxrate;
      } else {
        $taxcode = QBO::$standard_rated_taxcode;
        $taxRateRef = QBO::$standard_rated_purchases_taxrate;
      }

      $result = QuickbooksPurchase::getInstance()
        ->setRealmID($realmid)
        ->setTxnDate($data->txnDate)
        ->setEntity($data->entity)
        ->setBankAccount($data->bankAccount)
        ->setExpenseAccount($data->account)
        ->setPrivateNote(isset($data->privateNote)?$data->privateNote:'')
        ->setDescription(isset($data->description)?$data->description:'')
        ->setDocnumber(isset($data->docnumber)?$data->docnumber:'')
        ->setAmount($data->amount - $data->taxAmount) 
        ->setTaxAmount($data->taxAmount)
        ->setTaxCode($taxcode)
        ->setTaxRate($taxRateRef)
        ->create();

      if ($result) {
          echo json_encode(
              array("message" => "Purchase has been added for " . $data->txnDate . ".",
                  "id" => $result->Id)
            );
      }

    } catch (\Exception $e) {
      Error::response("Unable to create QB Purchase.", $e);
    }    
  }  
          
}