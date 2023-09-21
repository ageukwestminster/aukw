<?php

namespace Controllers;
/**
 * Controller to accomplish Shop related tasks. 
 *
 * @category  Controller
*/
class ShopCtl{


  /**
   * Return details of all Shops
   * 
   * @return void Output is echo'd directly to response 
   */
  public static function read_all(){  

    $model = new \Models\Shop();

    echo json_encode($model->read(), JSON_NUMERIC_CHECK);
  }


  /**
   * Return details of the Shop identified by $id
   *
   * @param int $id
   * 
   * @return void Output is echo'd directly to response
   * 
   */
  public static function read_one(int $id){  

    $model = new \Models\Shop();
    $model->id = $id;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }


  /**
   * Return details of the Shop identified by $name
   *
   * @param string $name
   * 
   * @return void Output is echo'd directly to response
   * 
   */
  public static function read_one_name(string $name){  

    $model = new \Models\Shop();
    $model->name = $name;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

}