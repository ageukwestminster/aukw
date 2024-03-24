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
   *  { "date": "2022-04-29", 
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

   /**
   * Return details of the QBO recurring transaction identified by $id
   *
   * @param int $id
   * @return void Output is echo'd directly to response 
   */
  public static function read_employee_allocations(){  

    if(!isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a value for the 'realmid' parameter.")
      );
      exit(1);
    } 

    $model = new \Models\QuickbooksRecurringTransaction();
    $model->id = \Core\Config::read('qb.allocationsid');
    $model->realmid = $_GET['realmid'];



    $response = $model->readone();

    if (isset($response) && isset($response->RecurringTransaction) && 
                    isset($response->RecurringTransaction->JournalEntry)) {
        $allocationTxnArray = $response->RecurringTransaction->JournalEntry->Line;
        $return = array();
        foreach ($allocationTxnArray as $line) {
            if (!isset($line->Description) || !preg_match('/ignore/i', $line->Description)) {
                $allocation = $line->JournalEntryLineDetail;
                $amount = $line->Amount;
                $employee = $allocation->Entity->EntityRef;
                $account = $allocation->AccountRef;
                $class = $allocation->ClassRef;

                if (!array_key_exists($employee->value, $return)) {
                    $return[$employee->value]= array();
                }

                $return[$employee->value][]= (object) [ 
                    'percentage' => $amount, 
                    'account' => $account,
                    'class' => $class
                ];
                
            }
          }
          echo json_encode($return, JSON_NUMERIC_CHECK); 
    };
    

  }
  

}