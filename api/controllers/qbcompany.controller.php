<?php

namespace Controllers;

use Models\QuickbooksAuth;

/**
 * Controller that provides methods to interrogate the QBO Company object.
 * 
 * @category  Controller
 */
class QBCompanyCtl{

  /**
   * Get information about the QBO company
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response
   */
  public static function companyInfo(string $realmid){  

    $model = new QuickbooksAuth();

    echo json_encode($model->companyInfo($realmid), JSON_NUMERIC_CHECK);
  }
}