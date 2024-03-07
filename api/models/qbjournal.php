<?php

namespace Models;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\JournalEntry;

use DateTime;

/**
 * Factory class that provides data about QBO General Journals.
 * 
 * @category Model
 */
class QuickbooksJournal{

  /**
   * Return details of the QBO general journal identified by $id
   * @param int $id The id of the ?journal to search for
   * @return IPPIntuitEntity Returns an journal with specified Id.
   * 
   */
  public function readOne(int $id, string $realmid){



    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($realmid);
    if ($dataService == false) {
      return;
    }

    $dataService->forceJsonSerializers();
    $journalentry = $dataService->FindbyId('journalentry', $id);
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