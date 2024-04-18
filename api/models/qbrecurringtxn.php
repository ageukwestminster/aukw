<?php

namespace Models;

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
  protected int $id;

  /**
   * The QBO company ID
   *
   * @var string
   */
  protected string $realmid;

  /**
   * ID setter
   */
  public function setId(int $id) {
    $this->id = $id;
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
   * ID getter.
   */
  public function getId() : string {
    return $this->id;
  }  

  /**
   * realmID getter.
   */
  public function getrealmId() : string {
    return $this->realmid;
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
   * Return details of the QBO recurring transaction identified by $id
   * @return object|false Returns an journal with specified Id.
   * 
   */
  public function readOne():object|false{

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);
    if ($dataService == false) {
      return false;
    }

    $dataService->forceJsonSerializers();
    $recurringTransaction = $dataService->FindbyId('recurringtransaction', $this->id);
    $error = $dataService->getLastError();
    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
        return false;
    }
    else {
      if (property_exists($recurringTransaction, 'RecurringTransaction')) {
        /** @disregard Intelephense error on next line */
        return $recurringTransaction->RecurringTransaction;
      } else {
        return $recurringTransaction;
      }
    }
  }

    /**
   * Return details of all the QBO recurring transactions in the company file
   * @return array An associative array of entities, keyed on QBO entity names such 
   * as 'JournalEntry, 'Bill' and others.
   * 
   */
  public function read():array{

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);
    if ($dataService == false) {
      return [];
    }
                        
    $transactions = $dataService->recurringTransaction('SELECT * FROM RecurringTransaction');
    $error = $dataService->getLastError();
    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
        return [];
    }
    else {
      if (property_exists($transactions, 'entities')) {
        /** @disregard Intelephense error on next line */
        return $transactions->entities;
      } else {
        return [];
      }
    }
  }

}