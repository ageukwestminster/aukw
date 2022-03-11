<?php

namespace Controllers;

class QBTokenCtl{

  public static function read_one($iduser){  

    $model = new \Models\QuickbooksToken();
    $model->iduser = $iduser;
    $model->read();

    echo json_encode($model, JSON_NUMERIC_CHECK);
  }


}