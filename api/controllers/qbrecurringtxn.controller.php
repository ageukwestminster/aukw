<?php

namespace Controllers;

/**
 * Controller to read details of QBO recurring transactions
 *
 * @category  Controller
*/
class QBRecurringTransactionCtl{

  /**
   * Return details of the QBO recurring transaction identified by $id
   * @param string $realmid The company ID for the QBO company.
   * @param int $id
   * @return void Output is echo'd directly to response 
   */
  public static function read_one(string $realmid, int $id){  

    $model = \Models\QuickbooksRecurringTransaction::getInstance()
      ->setRealmID($realmid)
      ->setId($id);  

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }


    /**
   * Return details of all the QBO recurring transactions in the company file
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function read_all(string $realmid){  

    $model = \Models\QuickbooksRecurringTransaction::getInstance()
      ->setRealmID($realmid);

    echo json_encode($model->read(), JSON_NUMERIC_CHECK);
  }
}