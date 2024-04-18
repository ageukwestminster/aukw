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
   * Return an array of QBO entities whose DocNumber starts with the provided string
   * 
   * More information: {@link https://developer.intuit.com/app/developer/qbo/docs/learn/explore-the-quickbooks-online-api/data-queries}
   * @param string $entity_type_name The QBO entity type name e.g. 'Bill' or 'JournalEntry'
   * @param string $doc_number The returned array of entities will have a DocNumber starting with this string
   * @return array Returns an array of entities that match the doc_number criterion
   */
  public function query_by_docnumber(string $entity_type_name, string $doc_number):array{

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);
    if ($dataService == false) {
      return [];
    }

    $entities = $dataService->Query("SELECT * FROM " . $entity_type_name 
        ." WHERE DocNumber LIKE '" . $doc_number . "%'");
    $error = $dataService->getLastError();
    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
    }
    else if ($entities) {
      return $entities;
    }

    return [];
    
  }

}