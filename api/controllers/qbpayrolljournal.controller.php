<?php

namespace Controllers;

use \Models\QuickbooksPayrollJournal;

/**
 * Controller to accomplish QBO Payroll Journal related tasks. 
 *
 * @category  Controller
*/
class QBPayrollJournalCtl{

  /**
   * Create a QBO sales receipt from data supplied via http POST
   * Sales items should be positive, Expenses and cash/credit cards are negative.
   * 
   * Sample data:
   *  { "date": "2022-04-29", "donations": { "number": 0, "sales": 0 }, 
   *   "cashDiscrepency": 0.05,"creditCards": -381.2,"cash": -183.30,
   *   "operatingExpenses": -1.3,"volunteerExpenses": -5,
   *   "clothing": { "number": 53, "sales": 310.50 },
   *   "brica": { "number": 75, "sales": 251.75 },
   *   "books": { "number": 4, "sales": 3.5 },
   *   "linens": { "number": 1, "sales": 5 },
   *   "cashToCharity": 0, "shopid": 1
   *  }
   *
   * @return void Output is echoed directly to response 
   * 
   */
  public static function create(){  

    if(!isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a value for the 'realmid' parameter.")
      );
      exit(1);
    } 

    $data = json_decode(file_get_contents("php://input"));

    try {
      $model = QuickbooksPayrollJournal::getInstance()
        ->setDocNumber($data->DocNumber)
        ->setTxnDate($data->TxnDate)
        ->setRealmID($_GET['realmid']
      );
    } catch (\Exception $e) {
    http_response_code(400);  
    echo json_encode(
      array(
        "message" => "Unable to enter payroll journal in Quickbooks. ",
        "extra" => $e->getMessage()
         )
        , JSON_NUMERIC_CHECK);
    exit(1);
  }

    if (!$model->validate()) {
      http_response_code(400);  
      echo json_encode(
        array(
          "message" => "Unable to enter payroll journal in Quickbooks. Transaction is not in balance for '" .
          $data->date . "'.")
          , JSON_NUMERIC_CHECK);
      exit(1);      
    }

    $result = $model->create();
    if ($result) {
        echo json_encode(
            array("message" => "Payroll journal with reference number '". $result['label']  ."' has been added for " . $result['date'] . ".",
                "id" => $result['id'])
          );
    }
  }

 

}