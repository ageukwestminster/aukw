<?php

namespace Controllers;

use \Models\QuickbooksBill;
use \Models\QuickbooksPensionBill;
use \Models\QuickbooksQuery;
use Core\QuickbooksConstants as QBO;
use Core\ErrorResponse as Error;
use Exception;

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
    try {
      $model = QuickbooksBill::getInstance()
        ->setRealmID($realmid)  
        ->setId($id);

      echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Unable to find QB Bill with id=$id.", $e);
    }
  }
  
  /**
   * Return an array of bills whose DocNumber starts with the given string.
   *
   * @param string $realmid The company ID for the QBO company.
   * @param string $doc_number The string to match e.g. 'Payroll_2024_03'
   * @return void Output is echo'd directly to response 
   */
  public static function query_by_docnumber(string $realmid, string $doc_number){  
    try {
      $bills = QuickbooksQuery::getInstance()
        ->setRealmID($realmid)
        ->query_by_docnumber('Bill', $doc_number); 

      echo json_encode($bills);
    } catch (Exception $e) {
      Error::response("Unable to find QB Bill with DocNumber=$doc_number.", $e);
    }
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
    try {
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
        throw new \Exception("Unable to delete QB bill with id=$id.");
      }
    } catch (Exception $e) {
      Error::response("Unable to delete QB bill.", $e);
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
    
    try{

      if ($realmid != QBO::CHARITY_REALMID) {
        throw new \Exception("Not implemented in Enterprises, this endpoint only exists for Charity QuickBooks.");
      }

      if(!isset($_GET['payrolldate']) || 
              !\Core\DatesHelper::validateDate($_GET['payrolldate'])) {
        throw new \Exception("Please supply a valid value for the 'payrolldate' parameter.");
      } else {
        $payrollDate = $_GET['payrolldate'];
      }

      $data = json_decode(file_get_contents("php://input"));

      // The Ref No. that appears on QBO ui. 
      // Format is "Payroll_YYYY_MM-LG" for a pension bill 
      $docNumber = QBO::payrollDocNumber($payrollDate).'-LG';

      $model = QuickbooksPensionBill::getInstance()
        ->setRealmID($realmid)
        ->setDocNumber($docNumber)
        ->setTxnDate($payrollDate)
        ->setSalarySacrificeTotal($data->salarySacrificeTotal)
        ->setEmployeePensContribTotal($data->employeePensionTotal)
        ->setTotal($data->total)
        ->setPensionCosts($data->pensionCosts);

      $result = $model->create();
      if ($result) {
          echo json_encode(
              array("message" => "Pension Bill has been added for " . $result['date'] . ".",
                  "id" => $result['id'])
            );
      }

    } catch (Exception $e) {
      Error::response("Unable to enter payroll bill in QuickBooks.", $e);
    }
  }  
}