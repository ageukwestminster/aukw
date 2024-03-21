<?php

namespace Models;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\JournalEntry;

use DateTime;

/**
 * Factory class that provides data about QBO recurring transactions.
 * 
 * @category Model
 */
class QuickbooksRecurringTransaction{

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
   * Return details of the QBO recurring transaction identified by $id
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
    $recurringTransaction = $dataService->FindbyId('recurringtransaction', $this->id);
    $error = $dataService->getLastError();
    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
    }
    else {
        return $recurringTransaction;
    }
  }

    /**
   * Return details of all the QBO recurring transactions in the company file
   * @return IPPIntuitEntity[] 
   * 
   */
  public function read(){

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);
    if ($dataService == false) {
      return;
    }
                        
    //$dataService->forceJsonSerializers();
    $transactions = $dataService->recurringTransaction('SELECT * FROM RecurringTransaction');
    $error = $dataService->getLastError();
    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
    }
    else {
        return $transactions;
    }
  }

}