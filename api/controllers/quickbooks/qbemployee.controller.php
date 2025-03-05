<?php

namespace Controllers;

use \Models\QuickbooksEmployee;
use \Core\ErrorResponse as Error;

/**
 * Controller to accomplish QBO Employee related tasks. 
 *
 * @category  Controller
*/
class QBEmployeeCtl{

  /**
   * Return details of the QBEmployee identified by $id
   * @param string $realmid The company ID for the QBO company.
   * @param int $id
   * @return void Output is echo'd directly to response 
   */
  public static function read_one(string $realmid, int $id){  
    try {
      $model = QuickbooksEmployee::getInstance()
        ->setId($id)
        ->setRealmID($realmid);   

      echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
    } catch (\Throwable $e) {
      Error::response("Unable to obtain list of employees from QuickBooks.", $e);
    }
  }

  /**
   * Return details of all QBO Employees. However who do not have an 
   * EmployeeID asigned to them are excluded from this list.
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function read_all(string $realmid){  

    try {
      $model = QuickbooksEmployee::getInstance()
        ->setRealmID($realmid); 

      echo json_encode(array_values($model->readAll()), JSON_NUMERIC_CHECK);

    } catch (\Throwable $e) {
      Error::response("Unable to obtain list of employees from QuickBooks.", $e);
    }
  }

}