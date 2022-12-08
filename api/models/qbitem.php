<?php

namespace Models;

class QuickbooksItem{


  public $id;
  

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