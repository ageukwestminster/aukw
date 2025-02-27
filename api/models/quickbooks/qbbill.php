<?php

namespace Models;

use QuickBooksOnline\API\Facades\Bill;
use QuickBooksOnline\API\Exception\SdkException;

/**
 * Factory class that provides data about QBO Bills.
 * 
 * @category Model
 */
class QuickbooksBill{
 
  /**
   * The QBO id of the Quickbooks Class.
   *
   * @var string
   */
  protected string $id;

  /**
   * The QBO company ID
   *
   * @var string
   */
  protected string $realmid;

  /**
   * The transaction date of the Journal entry
   *
   * @var string
   */
  protected string $TxnDate;  

  /**
   * The Reference number for the transaction. Does not have to be unique.
   *
   * @var string
   */
  protected string $DocNumber;  

  /**
   * Salary sacrifice total
   *
   * @var float
   */
  protected float $salarySacrificeTotal;  

  /**
   * Employee pension Contribution total
   *
   * @var float
   */
  protected float $employeePensContribTotal; 

  /**
   * ID setter
   */
  public function setId(int $id) {
    $this->id = $id;
    return $this;
  }

  /**
   * Transaction Date setter.
   */
  public function setTxnDate(string $txnDate) {
    $this->TxnDate = $txnDate;
    return $this;
  }

  /**
   * Reference number setter.
   */
  public function setDocNumber(string $docNumber) {
      $this->DocNumber = $docNumber;
      return $this;
  }

  /**
   * Private realmID setter.
   */
  public function setRealmID(string $realmid) {
    $this->realmid = $realmid;
    return $this;
  }

  /**
   * Salary Sacrifice Total setter
   */
  public function setSalarySacrificeTotal(float $salarySacrificeTotal) {
    $this->salarySacrificeTotal = $salarySacrificeTotal;
    return $this;
  }

  /**
   * Employee Pension Contribution Total setter
   */
  public function setEmployeePensContribTotal(float $employeePensContribTotal) {
    $this->employeePensContribTotal = $employeePensContribTotal;
    return $this;
  }

  /**
   * Reference number getter.
   */
  public function getDocNumber() : string {
    return $this->DocNumber;
  }

  /**
   * realmID getter.
   */
  public function getrealmId() : string {
      return $this->realmid;
  }

  /**
   * Transaction Date getter.
   */
  public function getTxnDate() : string {
      return $this->TxnDate;
  }

  /**
   * Constructor
   */
  protected function __construct(){}

  /**
   * Static constructor / factory
   */
  public static function getInstance() {
    return new self();
  }

  /**
   * Return details of the Bill identified by $id
   * 
   * @return IPPIntuitEntity Returns an item of specified Id.
   * 
   */
  public function readOne(){

      $auth = new QuickbooksAuth();
      $dataService = $auth->prepare($this->realmid);
      if ($dataService == false) {
        return;
      }

      $dataService->forceJsonSerializers();
      $item = $dataService->FindbyId('Bill', $this->id);
      $error = $dataService->getLastError();
      if ($error) {
        throw new SdkException("The Response message is: " . $error->getResponseBody());
      }
      else {
          return $item;
      }
  }

  /**
   * Push a new array describing a single line of a QBO bill into the given array
   * Helper function used in create.
   *
   * @param mixed $line_array The given array
   * @param string $description
   * @param float $amount
   * @param string $class
   * @param string $account
   * @param string $taxcode
   * 
   * @return void
   * 
   */
  protected function bill_line(&$line_array, $description, $amount, 
                                            $class, $account, $taxcode) {

    if (abs($amount) <= 0.005) return;

    array_push($line_array, array(
      "Description" => $description,
      "Amount" => $amount,
      "DetailType" => "AccountBasedExpenseLineDetail",
      "AccountBasedExpenseLineDetail" => [
        "AccountRef" => $account,
        "ClassRef" => $class,
        "TaxCodeRef" => $taxcode
      ]
    ));
  }

  /**
   * Delete a bill from the QB system.
   *
   * @return bool 'true' if success.
   * 
   */
  public function delete(): bool{
    $auth = new QuickbooksAuth();
    try{
      $dataService = $auth->prepare($this->realmid);
    }
    catch (\Exception $e) {
      http_response_code(401);  
      echo json_encode(
        array("message" =>  $e->getMessage() )
      );
      return false;
    }

    if ($dataService == false) {
      return false;
    }

    // Do not use $dataService->FindbyId to create the entity to delete
    // Use this simple representation instead
    // The problem is that FindbyId forces use of JSON and that doesnt work 
    // with the delete uri call
    $bill = Bill::create([
      "Id" => $this->id,
      "SyncToken" => "0"
    ]);
    
    $dataService->Delete($bill);

    $error = $dataService->getLastError();
    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
        return false;
    } else {      
      return true;
    }
  }  


}