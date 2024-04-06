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
}