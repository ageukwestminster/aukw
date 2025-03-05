<?php

namespace Controllers;
use \Core\ErrorResponse as Error;
use Exception;
/**
 * Controller to accomplish QBRealm related tasks. 
 *
 * @category  Controller
*/
class QBRealmCtl{


  /**
   * Return details of all QB Realms
   * 
   * @return void Output is echo'd directly to response 
   */
  public static function read_all(){  

    try {
      $includeSandbox = $_GET['includeSandbox'] ?? false; 

      $model = new \Models\QBRealm();

      echo json_encode($model->read($includeSandbox), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Unable to generate list of all realms.", $e);
    }
  }


  /**
   * Return details of the specifice QBO company identified by $realmid
   *
   * @param string $realmid The id of the QBO company.
   * 
   * @return void Output is echo'd directly to response.
   * 
   */
  public static function read_one(string $realmid){  
    try {
      $model = new \Models\QBRealm();
      $model->realmid = $realmid;

      echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Unable to show details of realm with realmid=$realmid.", $e);
    }
  }

}