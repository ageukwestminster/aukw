<?php

namespace Models;

use QuickBooksOnline\API\Facades\Bill;

/**
 * Factory class that provides a method to query QBO entities
 * 
 * @category Model
 */
class QuickbooksQuery{

  /**
   * The QBO company ID
   *
   * @var string
   */
  protected string $realmid;

  /**
   * Private realmID setter.
   */
  public function setRealmID(string $realmid) {
    $this->realmid = $realmid;
    return $this;
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
   * Return an array of QBO entities identified by $doc_number
   * @param string $doc_number
   * @return IPPIntuitEntity[] Returns an array of entities
   * 
   */
  public function query_by_docnumber(string $doc_number, string $entity_name){

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);
    if ($dataService == false) {
      return;
    }

    $entities = $dataService->Query("SELECT * FROM " . $entity_name ." WHERE DocNumber LIKE '" .
                                              $doc_number . "%'");
    $error = $dataService->getLastError();
    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
    }
    else {
        if ($entities) {
          return $entities;
        } else {
          return [];
        }
    }
  }

}