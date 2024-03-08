<?php

namespace Controllers;

/**
 * Controller to accomplish QBO Employee related tasks. 
 *
 * @category  Controller
*/
class QBEmployeeCtl{

  /**
   * Return details of the QBEmployee identified by $id
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

    $model = new \Models\QuickbooksEmployee();
    $model->id = $id;
    $model->realmid = $_GET['realmid'];

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

  /**
   * Return details of all QBO Employees
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

    $model = new \Models\QuickbooksEmployee();
    $model->realmid = $_GET['realmid'];

    echo json_encode($model->readAll(), JSON_NUMERIC_CHECK);
  }

}