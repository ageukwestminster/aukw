<?php

namespace Controllers;

use Core\QuickbooksConstants as QBO;
use \Models\QuickbooksEmployee;
use \Models\QuickbooksEmployerNIJournal;
use \Models\QuickbooksEnterprisesJournal;
use \Models\QuickbooksPayrollJournal;

/**
 * Controller to accomplish QBO Payroll Journal related tasks. 
 *
 * @category  Controller
*/
class QBPayrollJournalCtl{

  /**
   * Create the general journal entry for a single employee. The values used are those 
   * supplied in the body of the HTTP POST query.
   * 
   * The function expects a HTTP parameter called payrolldate.
   *
   * On success the PHP call exits with HTTP status 200 and a message confirming success.
   * If this fails the PHP call exits with HTTP status 400 and a message describing the error.
   * @param string $realmid The company ID for the QBO company.
   */
  public static function create_employee_payslip_journal(string $realmid):void{  

    QBPayrollJournalCtl::checkPayrollDate();

    $payrollDate = $_GET['payrolldate'];
    
    $data = json_decode(file_get_contents("php://input"));

    // The Ref No. that appears on QBO ui. 
    // Format is "Payroll_YYYY_MM-`${payroll_number}`" 
    $docNumber = QBO::payrollDocNumber($payrollDate).'-'.$data->payrollNumber;

    try {
      $model = QuickbooksPayrollJournal::getInstance()
        ->setDocNumber($docNumber)
        ->setTxnDate($payrollDate)
        ->setQuickbooksEmployeeId($data->quickbooksId)
        ->setGrossSalary($data->totalPay)
        ->setPAYE(empty($data->paye)?0:$data->paye)
        ->setEmployeeNI(empty($data->employeeNI)?0:$data->employeeNI)
        ->setOtherDeduction(empty($data->otherDeductions)?0:$data->otherDeductions)
        ->setSalarySacrifice(empty($data->salarySacrifice)?0:$data->salarySacrifice)
        ->setEmployeePension(empty($data->employeePension)?0:$data->employeePension)
        ->setStudentLoan(empty($data->studentLoan)?0:$data->studentLoan)
        ->setNetSalary($data->netPay)
        ->setRealmID($realmid);
      
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
          $data->employeeName . "'.")
          , JSON_NUMERIC_CHECK);
      exit(1);      
    }

    $result = $model->create_employee_journal();
    if ($result) {
        echo json_encode(
            array("message" => "Payroll journal for '". $data->employeeName  ."' has been added for " . $result['date'] . ".",
                "id" => $result['id'])
          );
    }
  }

  /**
   * Enter the journal txn for employer NI.
   * 
   * The function expects a HTTP parameter called payrolldate.
   * 
   * On success the PHP call exits with HTTP status 200 and a message confirming success.
   * If this fails the PHP call exits with HTTP status 400 and a message describing the error.
   * @param string $realmid The company ID for the QBO company.
   */
  public static function create_employer_ni_journal(string $realmid):void{

    QBPayrollJournalCtl::checkPayrollDate();

    $payrollDate = $_GET['payrolldate'];
    $docNumber = QBO::payrollDocNumber($payrollDate);
    $docNumber .= '-NI';

    $data = json_decode(file_get_contents("php://input"));

    if (!$data) {
      echo json_encode(
        array("message" => "Employer NI journal not added: No payslip data provided.")
      );
      exit(0);
    }

    try {

      $model = QuickbooksEmployerNIJournal::getInstance()
        ->setDocNumber($docNumber)
        ->setTxnDate($payrollDate)
        ->setRealmID($realmid);

      $result = $model->create_employerNI_journal($data);

      if ($result) {
        echo json_encode(
            array("message" => "Employer NI journal '". $result['label'] .
                        "' has been added for " . $result['date'] . ".",
                "id" => $result['id'])
          );
      } else {
        http_response_code(400);  
        echo json_encode(
          array(
            "message" => "Unable to enter payroll journal in Quickbooks."
          )
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
   * Create the general journal transaction in the Enterprises company file.
   * 
   * The function expects a HTTP parameter called payrolldate.
   * 
   * On success the PHP call exits with HTTP status 200 and a message confirming success.
   * If this fails the PHP call exits with HTTP status 400 and a message describing the error.
   * @param string $realmid The company ID for the QBO company.
   */
  public static function create_enterprises_journal(string $realmid):void {

    QBPayrollJournalCtl::checkPayrollDate();

    $payrollDate = $_GET['payrolldate'];
    $docNumber = QBO::payrollDocNumber($payrollDate);

    $data = json_decode(file_get_contents("php://input"));
    QBPayrollJournalCtl::checkPostBodyContent($data);

    try {

      $model = QuickbooksEnterprisesJournal::getInstance()
        ->setDocNumber($docNumber)
        ->setTxnDate($payrollDate)
        ->setRealmID($realmid);

      $result = $model->create_enterprises_journal($data);

      if ($result) {
        echo json_encode(
            array("message" => "Payroll journal for shop" .
                        "' has been added for " . $result['date'] . ".",
                "id" => $result['id'])
          );
      }

    } catch (\Exception $e) {
      http_response_code(400);  
      echo json_encode(
        array(
          "message" => "Unable to enter shop payroll journal in Quickbooks.",
          "extra" => $e->getMessage()
          )
          , JSON_NUMERIC_CHECK);
      exit(1);
    }   
  }

   /**
   * Return details of how employee salaries are to be split between various QBO classes. 
   * The values are saved in QBO as a recurring transaction. The ID of the recurring 
   * transaction is stored in Config.php as 'qb.allocationsid'.
   *
   * On success the PHP call exits with HTTP status 200 and a message confirming success.
   * If this fails the PHP call exits with HTTP status 400 and a message describing the error.
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function read_employee_allocations(string $realmid):void{  

    $employees = QuickbooksEmployee::getInstance()
      ->setRealmID($realmid)
      ->readAll();

    $model = \Models\QuickbooksRecurringTransaction::getInstance()
      ->setRealmID($realmid)
      ->setId(\Core\Config::read('qb.allocationsid'));

    $response = $model->readone();

    if (isset($response) && isset($response->JournalEntry) 
              && isset($response->JournalEntry->Line)) {

        $returnObj = array();
        
        try {

          foreach ($response->JournalEntry->Line as $line) {

            // Check for a line with no account name. This will show as a "DescriptionOnly" journal line
            if (isset($line->DetailType) && $line->DetailType == 'DescriptionOnly') {
              throw new \Exception("The recurring transaction is in an invalid state. Is there a line with a missing account name?");
            }
            
            if ( !isset($line->Description) || !preg_match('/ignore/i', $line->Description) ) {

              $amount = $line->Amount;
              
              $employee = $line->JournalEntryLineDetail->Entity->EntityRef;
              $account = $line->JournalEntryLineDetail->AccountRef;
              $class = $line->JournalEntryLineDetail->ClassRef;

              $returnObj[] = (object) [
                  'quickbooksId' => $employee->value,
                  'name' => $employee->name ?? '',
                  'payrollNumber' => 
                      array_key_exists($employee->value, $employees)?
                          $employees[$employee->value]['payrollNumber']:null,
                  'percentage' => $amount, 
                  'account' => property_exists($account,'value')?$account->value:$account,
                  'accountName' => $account->name ?? '',
                  'class' => (string)$class->value, // Force to string because its too long as a number
                  'className' => $class->name ?? '',
                  'isShopEmployee' => ($account->value??'') == QBO::AUEW_ACCOUNT
              ];
            }
          }

          echo json_encode($returnObj); 

        } catch (\Exception $e) {
          http_response_code(400);   
          echo json_encode(
              array("message" => "Unable to parse recurring transaction to obtain employee allocations." . $e->getMessage())
          );
          exit(1);
        }
          
        
    } else {
      http_response_code(400);   
      echo json_encode(
          array("message" => "Recurring transaction that is used to obtain employee allocations not found.",
          "details" => "QBO ID of recurring transaciton = " . $model->getId())
      );
      exit(1);
    }
    
  }

  /** 
   * Private function that checks that $_GET, the array of variables received via the HTTP 
   * GET method, contains a populated variable called 'payrolldate'. it also checks that
   * the value of this variable can be converted into a PHP Date.
   * 
   * If the test fails the PHP call exits with HTTP status 400 and a message describing the error.
  */
  private static function checkPayrollDate():void{
    if(!isset($_GET['payrolldate']) || empty($_GET['payrolldate']) ||
            !\Core\DatesHelper::validateDate($_GET['payrolldate'])) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a valid value for the 'payrolldate' parameter.")
      );
      exit(1);
    }
  }

  /**
   * Private function that checks that the supplied data variable exists and is of length != 0
   * 
   * If the test fails the PHP call exits with HTTP status 400 and a message describing the error.
   */
  private static function checkPostBodyContent($data):void{
    if(!$data || count($data) == 0) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "The body of the POST request seems empty.")
      );
      exit(1);
    }
  }
}