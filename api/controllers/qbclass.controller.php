<?php

namespace Controllers;

use \Datetime;

/**
 * Controller to accomplish QBO Class related tasks. 
 *
 * @category  Controller
*/
class QBClassCtl{

  /**
   * Return details of the QBClass identified by $id
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

    $model = new \Models\QuickbooksClass();
    $model->id = $id;
    $model->realmid = $_GET['realmid'];

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

  /**
   * Return details of all QBO Classes
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

    $model = new \Models\QuickbooksClass();
    $model->realmid = $_GET['realmid'];

    echo json_encode($model->readAll(), JSON_NUMERIC_CHECK);
  }

}