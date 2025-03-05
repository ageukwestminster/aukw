<?php

namespace Models;

use Exception;
use QuickBooksOnline\API\Exception\SdkException;
use QuickBooksOnline\API\Exception\ServiceException;

/**
 * Factory class that provides data about QBO Items. Items can also be known as Products.
 * 
 * @category Model
 */
class QuickbooksItem{
 
  /**
   * The QBO id of the Quickbooks Item.
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
   * Return details of the QBItem identified by $id
   *
   * @param int $id The QBO id of the Quickbooks Item.
   * 
   * @return object Returns an item of specified Id.
   * 
   */
  public function readOne():object{

      $auth = new QuickbooksAuth();
      $dataService = $auth->prepare($this->realmid);

      $dataService->forceJsonSerializers();
      $item = $dataService->FindbyId('Item', $this->id);
      $error = $dataService->getLastError();
      if ($error) {
        throw new SdkException("The QBO Response message is: " . $error->getResponseBody());
      }
      else {
        if (property_exists($item, 'Item')) {
          /** @disregard Intelephense error on next line */
          return $item->Item;
        } else {
          return $item;
        }
      }
  }

  /**
   * Return details of all QBO Items
   * @return array An array of QBO Items
   * @throws Exception 
   * @throws SdkException 
   * @throws ServiceException 
   */
  public function readAll():array{

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);

    $items = $dataService->FindAll('Item');
    $error = $dataService->getLastError();
    if ($error) {
      throw new SdkException("The QBO Response message is: " . $error->getResponseBody());
    }
    else {
        return $items;
    }
}


}