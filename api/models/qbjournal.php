<?php

namespace Models;

use QuickBooksOnline\API\Facades\JournalEntry;

/**
 * Factory class that provides data about QBO General Journals.
 * 
 * @category Model
 */
class QuickbooksJournal{

  /**
   * The QBO id of the Quickbooks Journal.
   *
   * @var int
   */
  public int $id;

  /**
   * The QBO company ID
   *
   * @var string
   */
  public string $realmid;

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
   * Reference number getter.
   */
  public function getDocNumber() : string {
      return $this->DocNumber;
  }

  /**
   * Transaction Date getter.
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
   * Return details of the QBO general journal identified by $id
   * @return IPPIntuitEntity Returns an journal with specified Id.
   * 
   */
  public function readOne(){

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);
    if ($dataService == false) {
      return;
    }

    $dataService->forceJsonSerializers();
    $journalentry = $dataService->FindbyId('journalentry', $this->id);
    $error = $dataService->getLastError();
    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
    }
    else {
        return $journalentry;
    }
  }

  /**
   * Return an array of QBO general journal entries identified by $doc_number
   * @param string $doc_number
   * @return IPPIntuitEntity[] Returns an array of General Journals
   * 
   */
  public function query_by_docnumber(string $doc_number){

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);
    if ($dataService == false) {
      return;
    }

    $journalarray = $dataService->Query("SELECT * FROM JournalEntry WHERE DocNumber LIKE '" .
                                              $doc_number . "%'");
    $error = $dataService->getLastError();
    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
    }
    else {
        return $journalarray;
    }
  }

  /**
   * Delete a journal from the QB system.
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
    $journal = JournalEntry::create([
      "Id" => $this->id,
      "SyncToken" => "0"
    ]);
    
    $dataService->Delete($journal);

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

  /**
   * Push a new array describing a single line of a QBO journal into the given array
   * Helper function used in create.
   *
   * @param mixed $line_array The given array
   * @param mixed $description
   * @param mixed $amount
   * @param mixed $employee
   * @param mixed $class
   * @param mixed $account
   * 
   * @return void
   * 
   */
  protected function payrolljournal_line(&$line_array, $description, $amount, $employee, $class, $account) {
    if (abs($amount) <= 0.005) return;

    array_push($line_array, array(
      "Description" => $description,
      "Amount" => abs($amount),
      "DetailType" => "JournalEntryLineDetail",
      "JournalEntryLineDetail" => [
        "PostingType" => ($amount<0?"Credit":"Debit"),
        "Entity" => [
            "Type" => "Employee",
            "EntityRef" => $employee
        ],
        "AccountRef" => $account,
        "ClassRef" => $class,
      ]
    ));
  }
}