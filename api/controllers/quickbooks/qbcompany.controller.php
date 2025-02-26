<?php

namespace Controllers;

use Models\QuickbooksAuth;

/**
 * Controller that provides methods to interrogate the QBO Company object. Currently
 * read only.
 * 
 * @category  Controller
 */
class QBCompanyCtl{

  /**
   * Get information about the QB company for a given realmID. Includes billing
   * and postal addresses and emails, subscription details and SIC code, inter alia.
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response
   */
  public static function companyInfo(string $realmid){  

    $model = new QuickbooksAuth();

    echo json_encode($model->companyInfo($realmid), JSON_NUMERIC_CHECK);
  }
}