<?php

namespace Controllers;

use Models\QuickbooksAuth;

/**
 * Controller that provides methods to interrogate the QBO Copmpany object.
 * 
 * @category  Controller
 */
class QBCompanyCtl{

  /**
   * Get information about the QBO company
   * 
   * @return void Output is echo'd directly to response
   */
  public static function companyInfo(){  

    if( !isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
          array("message" => "Please supply realmid as a parameter.")
      );
      exit(1);
    } 

    $realmid = $_GET['realmid'];

    $model = new QuickbooksAuth();

    echo json_encode($model->companyInfo($realmid), JSON_NUMERIC_CHECK);
  }
}