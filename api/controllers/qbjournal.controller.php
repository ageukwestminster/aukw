<?php

namespace Controllers;

class QBJournalCtl{

  public static function read_one($id){  

    $model = new \Models\QuickbooksJournal();
    $model->id = $id;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

}