<?php

namespace Controllers;

/**
 * Provide the read_one method to query QBO for transactions of type General Journal
 */
class QBJournalCtl{

    /**
   * Return details of the QBItem identified by $id
   *
   * @param int $id
   * @return void 
   */
  public static function read_one(int $id){  

    $model = new \Models\QuickbooksJournal();
    $model->id = $id;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

}