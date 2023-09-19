<?php

namespace Controllers;

/**
 * Controller to accomplish QBO General Journal related tasks. 
 *
 * @category  Controller
*/
class QBJournalCtl{

  /**
   * Return details of the QBO general journal identified by $id
   *
   * @param int $id
   * @return void Output is echo'd directly to response 
   */
  public static function read_one(int $id){  

    $model = new \Models\QuickbooksJournal();
    $model->id = $id;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

}