<?php

namespace Controllers;

class JournalCtl{


  public static function read_one($id){  

    $model = new \Models\QuickbooksJournal();
    $model->id = $id;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

  public static function create(){  

    $model = new \Models\QuickbooksJournal();

    $result = $model->create();
    if ($result) {
        echo json_encode(
            array("message" => "Journal '". $result['label']  ."' has been added for " . $result['date'] . ".",
                "id" => $result['id'])
          );
    }
  }

}