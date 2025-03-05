<?php

namespace Controllers;

use \Core\ErrorResponse as Error;
use Exception;

/**
 * Controller to accomplish QBO Item related tasks. 
 *
 * @category  Controller
*/
class QBItemCtl{

  /**
   * Return details of the QBItem identified by $id
   * @param string $realmid The company ID for the QBO company.
   * @param int $id The QBO Id of the item to search for.
   * @return void Output is echo'd directly to response 
   */
  public static function read_one(string $realmid, int $id){  
    try {
      $model = \Models\QuickbooksItem::getInstance()
        ->setRealmID($realmid)
        ->setId($id);  

      echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Unable to read QBO Item with id=$id.", $e);
    }
  }

   /**
    * Return details of all QBO Items
    * @param string $realmid The company ID for the QBO company.
    * @return void Output is echo'd directly to response
    */
  public static function read_all(string $realmid){  
    try {
      $model = \Models\QuickbooksItem::getInstance()
        ->setRealmID($realmid);

      echo json_encode($model->readAll(), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Unable to generate list of QBO Items.", $e);
    }
  }

}