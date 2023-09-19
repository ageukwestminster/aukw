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
   *
   * @param int $id
   * @return void Output is echo'd directly to response 
   */
  public static function read_one(int $id){  

    $model = new \Models\QuickbooksItem();
    $model->id = $id;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

  /**
   * Return details of all QBO Items
   * 
   * @return void Output is echo'd directly to response 
   */
  public static function read_all(){  

    $model = new \Models\QuickbooksItem();

    echo json_encode($model->readAll(), JSON_NUMERIC_CHECK);
  }

}