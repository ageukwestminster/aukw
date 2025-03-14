<?php

namespace Controllers;

use \Models\QuickbooksBill;
use \Models\QuickbooksPensionBill;
use \Models\QuickbooksQuery;
use Core\QuickbooksConstants as QBO;

/**
 * Controller to accomplish QBO Bill (or invoice) related tasks. 
 *
 * @category  Controller
*/
class QBBillCtl{

  /**
   * Return details of the QBBill identified by $id
   *
   * @param string $realmid The company ID for the QBO company.
   * @param string $id
   * @return void Output is echo'd directly to response 
   */
  public static function read_one(string $realmid, string $id){  

    $model = QuickbooksBill::getInstance()
      ->setRealmID($realmid)  
      ->setId($id);

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }
  
  /**
   * Return an array of bills whose DocNumber starts with the given string.
   *
   * @param string $realmid The company ID for the QBO company.
   * @param string $doc_number The string to match e.g. 'Payroll_2024_03'
   * @return void Output is echo'd directly to response 
   */
  public static function query_by_docnumber(string $realmid, string $doc_number){  

    $bills = QuickbooksQuery::getInstance()
      ->setRealmID($realmid)
      ->query_by_docnumber('Bill', $doc_number); 

    echo json_encode($bills);
  }

  /**
   * Delete from QBO the bill identified by $id
   *
   * On success the PHP call exits with HTTP status 200 and a message confirming success.
   * If this fails the PHP call exits with HTTP status 400 and a message describing the error.
   * @param string $realmid The company ID for the QBO company.
   * @param int $id The QBO id, not the DocNumber
   * @return void Output is echoed directly to response 
   */
  public static function delete(string $realmid, int $id){  

    $model = QuickbooksBill::getInstance()
      ->setId($id)
      ->setRealmID($realmid); 

    if($model->delete()) {
      echo json_encode(
        array(
          "message" => "Bill with id=$id was deleted.",
          "id" => $id)
          , JSON_NUMERIC_CHECK);
    } else{
        http_response_code(400);  
        echo json_encode(
          array(
            "message" => "Unable to delete QB bill.",
            "id" => $id)
            , JSON_NUMERIC_CHECK);
    }
  }  

  /**
   * Create a QBO bill for the monthly pension expenses from data supplied via http POST
   *
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echoed directly to response 
   * 
   */
  public static function create_pensions_bill(string $realmid){  
    
    if(!isset($_GET['payrolldate']) || 
            !\Core\DatesHelper::validateDate($_GET['payrolldate'])) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a valid value for the 'payrolldate' parameter.")
      );
      exit(1);
    } else {
      $payrollDate = $_GET['payrolldate'];
    }

    $data = json_decode(file_get_contents("php://input"));

    // The Ref No. that appears on QBO ui. 
    // Format is "Payroll_YYYY_MM-LG" for a pension bill 
    $docNumber = QBO::payrollDocNumber($payrollDate).'-LG';

    try {

      $model = QuickbooksPensionBill::getInstance()
        ->setRealmID($realmid)
        ->setDocNumber($docNumber)
        ->setTxnDate($payrollDate)
        ->setSalarySacrificeTotal($data->salarySacrificeTotal)
        ->setEmployeePensContribTotal($data->employeePensionTotal)
        ->setTotal($data->total)
        ->setPensionCosts($data->pensionCosts);
        
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

    $result = $model->create();
    if ($result) {
        echo json_encode(
            array("message" => "Pension Bill has been added for " . $result['date'] . ".",
                "id" => $result['id'])
          );
    }
  }  
}