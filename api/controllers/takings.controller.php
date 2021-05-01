<?php

namespace Controllers;

class TakingsCtl{

  public static function read_one($id){  

    $model = new \Models\Takings();
    $model->id = $id;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

}