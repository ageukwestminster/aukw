<?php

namespace Models;

/**
 * Factory class that provides data about QBO Classes.
 * 
 * @category Model
 */
class QuickbooksClass{
 
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
   * Return details of the QBClass identified by $id
   * 
   * @return object|false Returns an item of specified Id.
   * 
   */
  public function readOne():object|false{

      $auth = new QuickbooksAuth();
      $dataService = $auth->prepare($this->realmid);
      if ($dataService == false) {
        return false;
      }

      $dataService->forceJsonSerializers();
      $item = $dataService->FindbyId('Class', $this->id);
      $error = $dataService->getLastError();
      if ($error) {
          echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
          echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
          echo "The Response message is: " . $error->getResponseBody() . "\n";
          return false;
      }
      else {
        if (property_exists($item, 'Class')) {
          /** @disregard Intelephense error on next line */
          return $item->Class;
        } else {
          return $item;
        }
      }
  }

  /**
   * Return details of all QBO Classes
   * 
   * @return array An array of QBO Classes
   * 
   */
  public function readAll():array{

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);
    if ($dataService == false) {
      return [];
    }

    //$dataService->forceJsonSerializers();
    $items = $dataService->FindAll('Class');
    $error = $dataService->getLastError();
    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
        return [];
    }
    else {
        return $items;
    }
}


}