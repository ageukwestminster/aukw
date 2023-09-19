<?php

namespace Models;

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
  public int $id;

  /**
   * Return details of the QBItem identified by $id
   *
   * @param int $id The QBO id of the Quickbooks Item.
   * 
   * @return IPPIntuitEntity Returns an item of specified Id.
   * 
   */
  public function readOne(){

      $auth = new QuickbooksAuth();
      $dataService = $auth->prepare();
      if ($dataService == false) {
        return;
      }

      $dataService->forceJsonSerializers();
      $item = $dataService->FindbyId('Item', $this->id);
      $error = $dataService->getLastError();
      if ($error) {
          echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
          echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
          echo "The Response message is: " . $error->getResponseBody() . "\n";
      }
      else {
          return $item;
      }
  }

  /**
   * Return details of all QBO Items
   * 
   * @return array An array of QBO Items
   * 
   */
  public function readAll(){

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare();
    if ($dataService == false) {
      return;
    }

    //$dataService->forceJsonSerializers();
    $items = $dataService->FindAll('Item');
    $error = $dataService->getLastError();
    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
    }
    else {
        return $items;
    }
}


}