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
   * Enter the journal txn for employer ni, v vm, v, 
   */
  public static function create_employerni(){
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
                    
      $newJournal = $response->RecurringTransaction->JournalEntry;
    }
  }

   /**
   * Return details of the QBO recurring transaction identified by $id
   *
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
        
        try {

          $returnObj = array();

          foreach ($allocationTxnArray as $line) {

            if (!isset($line->Description) || !preg_match('/ignore/i', $line->Description)) {

              $amount = $line->Amount;
              
              $employee = $line->JournalEntryLineDetail->Entity->EntityRef;
              $account = $line->JournalEntryLineDetail->AccountRef;
              $class = $line->JournalEntryLineDetail->ClassRef;

              if (!array_key_exists($employee->value, $returnObj)) {
                  $returnObj[$employee->value] = array();
                  $returnObj[$employee->value]['id'] = $employee->value;
                  $returnObj[$employee->value]['name'] = $employee->name ?? '';
                  $returnObj[$employee->value]['allocations'] = array();
              }

              $returnObj[$employee->value]['allocations'][] = (object) [
                  'percentage' => $amount, 
                  'account' => $account,
                  'class' => $class
              ];
            }
          }

          // Check allocations sum up to 100 for each employee
          foreach ($returnObj as $employeeAllocationsObj) {  
            $sum = 0;     
            foreach ($employeeAllocationsObj['allocations'] as $allocation) {
              $sum += $allocation->percentage;
            }     
            if (abs($sum - 100) > 0.0005) {
              throw new \Exception("Sum of percentage allocations for employee named '" 
                . $employeeAllocationsObj->name . "' (QBO id = " 
                . $employeeAllocationsObj->id . ") do not add up to 100.");
            }
          }

          // array_values converts associative array to normal array
          echo json_encode(array_values($returnObj), JSON_NUMERIC_CHECK); 

        } catch (\Exception $e) {
          http_response_code(400);   
          echo json_encode(
              array("message" => "Unable to parse recurring transaction to obtain employee allocations.",
              "details" => $e->getMessage())
          );
          exit(1);
        }
          
        
    } else {
      http_response_code(400);   
      echo json_encode(
          array("message" => "Recurring transaction that is used to obtain employee allocations not found.",
          "details" => "QBO ID of recurring transaciton = " . $model->id)
      );
      exit(1);
    }
    
  }
  

}