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
   * @param string $realmid The company ID for the QBO company.
   * @param string $id
   * @return void Output is echo'd directly to response 
   */
  public static function read_one(string $realmid, string $id){  

    $model = \Models\QuickbooksClass::getInstance()
      ->setRealmID($realmid)
      ->setId($id);      

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

  /**
   * Return details of all QBO Classes
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function read_all(string $realmid){  

    $model = \Models\QuickbooksClass::getInstance()
      ->setRealmID($realmid);

    echo json_encode($model->readAll(), JSON_NUMERIC_CHECK);
  }

}