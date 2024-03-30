<?php

namespace Models;

/**
 * Factory class that provides data about QBO Employees.
 * 
 * @category Model
 */
class QuickbooksEmployee{
 
  /**
   * The QBO id of the Quickbooks Employee.
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
   * Return details of the QBEmployee identified by $id
   *
   * @param int $id The QBO id of the Quickbooks Item.
   * 
   * @return IPPIntuitEntity Returns an item of specified Id.
   * 
   */
  public function readOne(){

      $auth = new QuickbooksAuth();
      $dataService = $auth->prepare($this->realmid);
      if ($dataService == false) {
        return;
      }

      $dataService->forceJsonSerializers();
      $item = $dataService->FindbyId('Employee', $this->id);
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
   * Return details of all QBO Employees
   * 
   * @return array An array of QBO Employees
   * 
   */
  public function readAll(){

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);
    if ($dataService == false) {
      return;
    }

    $items = $dataService->FindAll('Employee');
    $error = $dataService->getLastError();
    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
    }
    else {

        $employeeArray = array();
        foreach ($items as $item) {
          $employee = array(
            "quickbooksId" => $item->Id,
            "name" => $item->DisplayName,
            "payrollNumber" => $item->EmployeeNumber
          );
          $employeeArray[] = $employee;
        }

        return $employeeArray;
    }
}


}