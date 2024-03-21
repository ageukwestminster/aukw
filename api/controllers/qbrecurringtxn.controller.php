<?php

namespace Controllers;

/**
 * Controller to read details of QBO recurring transactions
 *
 * @category  Controller
*/
class QBRecurringTransactionCtl{

  /**
   * Return details of the QBO recurring transaction identified by $id
   *
   * @param int $id
   * @return void Output is echo'd directly to response 
   */
  public static function read_one(int $id){  

    if(!isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a value for the 'realmid' parameter.")
      );
      exit(1);
    } 

    $model = new \Models\QuickbooksRecurringTransaction();
    $model->id = $id;
    $model->realmid = $_GET['realmid'];

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }


    /**
   * Return details of all the QBO recurring transactions in the company file
   *
   * @return void Output is echo'd directly to response 
   */
  public static function read_all(){  

    if(!isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a value for the 'realmid' parameter.")
      );
      exit(1);
    } 

    $model = new \Models\QuickbooksRecurringTransaction();
    $model->realmid = $_GET['realmid'];

    echo json_encode($model->read(), JSON_NUMERIC_CHECK);
  }
}