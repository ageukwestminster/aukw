<?php

namespace Models;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\JournalEntry;

use DateTime;

/**
 * Factory class that provides data about QBO General Journals.
 */
class QuickbooksJournal{

  /**
   * The QBO id of the Quickbooks General Journal.
   *
   * @var int
   */
  public int $id;

  /**
   * Return details of the QBO general journal identified by $id
   *
   * @param int $id The QBO id of the Quickbooks general journal.
   * 
   * @return IPPIntuitEntity Returns an journal with specified Id.
   * 
   */
  public function readOne(){

      $auth = new QuickbooksAuth();
      $dataService = $auth->prepare();
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

}