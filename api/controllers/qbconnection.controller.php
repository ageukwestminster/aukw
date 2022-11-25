<?php

namespace Controllers;

class QBTokenCtl{

  public static function read_one(){  

    $model = new \Models\QuickbooksToken();
    $model->read();

    echo json_encode($model, JSON_NUMERIC_CHECK);
  }


}