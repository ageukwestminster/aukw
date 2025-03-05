<?php

namespace Controllers;

use \Models\QuickbooksTransfer;
use Core\QuickbooksConstants as QBO;
use \Core\ErrorResponse as Error;
use Exception;

/**
 * Controller to accomplish QBO Transfer related tasks. 
 *
 * @category  Controller
*/
class QBTransferCtl{

  /**
   * Return details of the QBO Transfer identified by $id
   *
   * @param string $realmid The company ID for the QBO company.
   * @param string $id
   * @return void Output is echo'd directly to response 
   */
  public static function read_one(string $realmid, string $id){  
    try {
    $model = QuickbooksTransfer::getInstance()
      ->setRealmID($realmid)  
      ->setId($id);

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
    } catch (\Exception $e) {
      Error::response("Unable to retrieve QB Transfer with id=$id.", $e);
    }
  }

  /**
   * Delete from QBO the transfer identified by $id
   *
   * On success the PHP call exits with HTTP status 200 and a message confirming success.
   * If this fails the PHP call exits with HTTP status 400 and a message describing the error.
   * @param string $realmid The company ID for the QBO company.
   * @param int $id The QBO id, not the DocNumber
   * @return void Output is echoed directly to response 
   */
  public static function delete(string $realmid, int $id){  

    try {

      $model = QuickbooksTransfer::getInstance()
        ->setId($id)
        ->setRealmID($realmid); 

      if($model->delete()) {
        echo json_encode(
          array(
            "message" => "Transfer with id=$id was deleted.",
            "id" => $id)
            , JSON_NUMERIC_CHECK);
      } 
    
    } catch (\Exception $e) {
      Error::response("Unable to delete QB Transfer with id=$id.", $e);
    }
  }  

  /**
   * Create a QBO transfer from data supplied via http POST
   *
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echoed directly to response 
   * 
   */
  public static function create(string $realmid){  
    
    try{

      /*if ($realmid != QBO::ENTERPRISES_REALMID) {
        throw new \Exception("Not implemented in Charity, this endpoint only exists for Enterprises QuickBooks.");
      }*/

      $data = json_decode(file_get_contents("php://input"));

      if (!isset($data->date)) {
        throw new \InvalidArgumentException("'date' property is missing from POST body.");
      } else if (!\Core\DatesHelper::validateDate($data->date) ) {
        throw new \InvalidArgumentException("'date' property is not in the correct format. Value provided: $data->date, expect yyyy-mm-dd format.");
      } else if (!isset($data->fromaccountno)) {
        throw new \InvalidArgumentException("'fromaccountno' property is missing from POST body.");
      } else if (!isset($data->toaccountno)) {
        throw new \InvalidArgumentException("'toaccountno' property is missing from POST body.");
      } else if ($data->toaccountno == $data->fromaccountno) {
        throw new \InvalidArgumentException("'toaccountno' must be different from 'fromaccountno'.");
      }  else if (!isset($data->amount)) {
        throw new \InvalidArgumentException("'amount' property is missing from POST body.");
      } else if ($data->amount <= 0) {
        throw new \InvalidArgumentException("'amount' property must be greater than zero.");
      }

      $result = QuickbooksTransfer::getInstance()
        ->setRealmID($realmid)
        ->setTxnDate($data->date)
        ->setFromAccountNo($data->fromaccountno)
        ->setToAccountNo($data->toaccountno)
        ->setPrivateNote(isset($data->note)?$data->note:'')
        ->setAmount($data->amount)
        ->create();

      if ($result) {
          echo json_encode(
              array("message" => "Transfer has been added for " . $data->date . ".",
                  "id" => $result->Id)
            );
      }

    } catch (\Exception $e) {
      Error::response("Unable to create Transfer in Quickbooks.", $e);
    }    
  }  
  /**
   * Create a QBO transfer from data supplied via http POST
   * 
   * A positive amount is a transfer from interco to 'Paid by Parent.
   * A negative amount is a transfer to interco from 'Paid by Parent.
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echoed directly to response 
   * 
   */
  public static function create_enterprises_interco(string $realmid){  
    
    try{

      if ($realmid != QBO::ENTERPRISES_REALMID) {
        throw new \Exception("Not implemented in Charity, this endpoint exists for Enterprises QuickBooks only.");
      }

      $data = json_decode(file_get_contents("php://input"));

      if (!isset($data->date)) {
        throw new \InvalidArgumentException("'date' property is missing from POST body.");
      } else if (!\Core\DatesHelper::validateDate($data->date) ) {
        throw new \InvalidArgumentException("'date' property is not in the correct format. Value provided: $data->date, expect yyyy-mm-dd format.");
      } else if (!isset($data->amount)) {
        throw new \InvalidArgumentException("'amount' property is missing from POST body.");
      } else if ($data->amount == 0) {
        throw new \InvalidArgumentException("'amount' property must be non-zero.");
      }

      if ($data->amount > 0) {
        $toAccountNo = QBO::AUEW_PAIDBYPARENT_ACCOUNT;
        $fromAccountNo = QBO::AUKW_INTERCO_ACCOUNT;
      } else {
        $fromAccountNo = QBO::AUEW_PAIDBYPARENT_ACCOUNT;
        $toAccountNo = QBO::AUKW_INTERCO_ACCOUNT;
      }

      $result = QuickbooksTransfer::getInstance()
        ->setRealmID($realmid)
        ->setTxnDate($data->date)
        ->setFromAccountNo($fromAccountNo)
        ->setToAccountNo($toAccountNo)
        ->setPrivateNote(isset($data->note)?$data->note:'')
        ->setAmount($data->amount)
        ->create();

      if ($result) {
          echo json_encode(
              array("message" => "Interco transfer has been added for " . $data->date . ".",
                  "id" => $result->Id)
            );
      }

    } catch (\Exception $e) {
      Error::response("Unable to create interco transfer in Quickbooks.", $e);
    }    
  }  
}