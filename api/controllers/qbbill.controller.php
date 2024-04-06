<?php

namespace Controllers;

use \Models\QuickbooksBill;

/**
 * Controller to accomplish QBO Bill (or invoice) related tasks. 
 *
 * @category  Controller
*/
class QBBillCtl{

  /**
   * Return details of the QBBill identified by $id
   *
   * @param string $id
   * @return void Output is echo'd directly to response 
   */
  public static function read_one(string $id){  

    if(!isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a value for the 'realmid' parameter.")
      );
      exit(1);
    } 

    $model = QuickbooksBill::getInstance()
      ->setId($id)
      ->setRealmID($_GET['realmid']);    

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

  /**
   * Delete from QBO the bill identified by $id
   *
   * @param int $id The QBO id, not the DocNumber
   * @return void Output is echoed directly to response 
   */
  public static function delete(int $id){  
    
    if(!isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a value for the 'realmid' parameter.")
      );
      exit(1);
    } 

    $model = QuickbooksBill::getInstance()
      ->setId($id)
      ->setRealmID($_GET['realmid']); 

    if($model->delete()) {
      echo json_encode(
        array(
          "message" => "Bill with id=$id was deleted.",
          "id" => $id)
          , JSON_NUMERIC_CHECK);
    } else{
        http_response_code(400);  
        echo json_encode(
          array(
            "message" => "Unable to delete QB bill.",
            "id" => $id)
            , JSON_NUMERIC_CHECK);
    }
  }  
}