<?php

namespace Controllers;

use DateTime;
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

    $result = $model->create_employee_journal();
    if ($result) {
        echo json_encode(
            array("message" => "Payroll journal with reference number '". $result['label']  ."' has been added for " . $result['date'] . ".",
                "id" => $result['id'])
          );
    }
  }

  /**
   * Enter the journal txn for employer NI
   */
  public static function create_employerni(){
    if(!isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a value for the 'realmid' parameter.")
      );
      exit(1);
    }
    
    if(!isset($_GET['payrolldate']) || 
            !\Core\DatesHelper::validateDate($_GET['payrolldate'])) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a valid value for the 'payrolldate' parameter.")
      );
      exit(1);
    } else {
      $payrollDate = $_GET['payrolldate'];
      $docNumber = QBPayrollJournalCtl::payrollDocNumber($payrollDate);
      $docNumber .= '-NI';
    }

    $data = json_decode(file_get_contents("php://input"));

    try {

      $model = QuickbooksPayrollJournal::getInstance()
        ->setDocNumber($docNumber)
        ->setTxnDate($payrollDate)
        ->setRealmID($_GET['realmid']);

      $result = $model->create_employerNI_journal($data);

      if ($result) {
        echo json_encode(
            array("message" => "Employer NI journal '". $result['label'] .
                        "' has been added for " . $result['date'] . ".",
                "id" => $result['id'])
          );
      }

    } catch (\Exception $e) {
    http_response_code(400);  
    echo json_encode(
      array(
        "message" => "Unable to enter payroll journal in Quickbooks.",
        "extra" => $e->getMessage()
         )
        , JSON_NUMERIC_CHECK);
    exit(1);
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

    $employeeModel = new \Models\QuickbooksEmployee();
    $employeeModel->realmid = $_GET['realmid'];
    $employees = $employeeModel->readAll();

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

              $returnObj[] = (object) [
                  'id' => $employee->value,
                  'name' => $employee->name ?? '',
                  'payrollNumber' => 
                      array_key_exists($employee->value, $employees)?
                          $employees[$employee->value]['payrollNumber']:null,
                  'percentage' => $amount, 
                  'account' => $account->value,
                  'class' => (string)$class->value
              ];
            }
          }

          // Check allocations sum up to 100 for each employee
          /*foreach ($returnObj as $employeeAllocationsObj) {  
            $sum = 0;     
            foreach ($employeeAllocationsObj['allocations'] as $allocation) {
              $sum += $allocation->percentage;
            }     
            if (abs($sum - 100) > 0.0005) {
              throw new \Exception("Sum of percentage allocations for employee named '" 
                . $employeeAllocationsObj->name . "' (QBO id = " 
                . $employeeAllocationsObj->id . ") do not add up to 100.");
            }
          }*/

          // array_values converts associative array to normal array
          echo json_encode($returnObj); 

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
  
  /**
   * Helper function to regularise the DocNumber for payroll transactions
   * @param string $payrollDate A string representation of the date of the 
   * payroll in 'YYYY-mm-dd' format.
   * @return string 
   */
  private static function payrollDocNumber(string $payrollDate) : string {
    $d = DateTime::createFromFormat('Y-m-d', $payrollDate);
    return 'Payroll_' . $d->format('Y_m');
  }
}