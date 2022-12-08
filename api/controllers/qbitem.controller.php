<?php

namespace Controllers;

use \Datetime;

class QBItemCtl{

  public static function read_one($id){  

    $model = new \Models\QuickbooksItem();
    $model->id = $id;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

  public static function read_all(){  

    $model = new \Models\QuickbooksItem();

    echo json_encode($model->readAll(), JSON_NUMERIC_CHECK);
  }

}