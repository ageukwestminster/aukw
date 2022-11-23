<?php

namespace Controllers;

use \Datetime;

class JournalCtl{

  const NOT_IN_QUICKBOOKS = 0;

  public static function read_one($id){  

    $model = new \Models\QuickbooksJournal();
    $model->id = $id;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

}