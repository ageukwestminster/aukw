<?php

namespace Controllers;

use \Datetime;

/**
 * Controller to accomplish QBO Item related tasks. 
 *
 * @category  Controller
*/
class QBItemCtl{

  /**
   * Return details of the QBItem identified by $id
   * @param string $realmid The company ID for the QBO company.
   * @param int $id
   * @return void Output is echo'd directly to response 
   */
  public static function read_one(string $realmid, int $id){  

    $model = \Models\QuickbooksItem::getInstance()
      ->setRealmID($realmid)
      ->setId($id);  

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

  /**
   * Return details of all QBO Items
   * 
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function read_all(string $realmid){  

    $model = \Models\QuickbooksItem::getInstance()
      ->setRealmID($realmid);

    echo json_encode($model->readAll(), JSON_NUMERIC_CHECK);
  }

}