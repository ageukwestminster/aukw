<?php

namespace Controllers;
/**
 * Controller to accomplish Shop related tasks. 
 *
 * @category  Controller
*/
class ShopCtl{


  public static function read_all(){  

    $model = new \Models\Shop();

    echo json_encode($model->read(), JSON_NUMERIC_CHECK);
  }


  public static function read_one($id){  

    $model = new \Models\Shop();
    $model->id = $id;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }


  public static function read_one_name($name){  

    $model = new \Models\Shop();
    $model->name = $name;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

}