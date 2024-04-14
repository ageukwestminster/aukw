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
   * realmID getter.
   */
  public function getrealmId() : string {
    return $this->realmid;
  }

  /**
   * Id getter.
   */
  public function getId() : int {
    return $this->id;
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
   * @return array An array of QBO Employees, associated by QBO Id
   * 
   */
  public function readAll(){
    return $this->readAllImpl(false);
  }

  /**
   * Return details of all QBO Employees
   * 
   * @return array An array of QBO Employees, associated by Name
   * 
   */
  public function readAllAssociatedByName(){
    return $this->readAllImpl(true);
  }

  /**
   * Return details of all QBO Employees
   * @param bool $associateByName If 'true' return an associative array, sorted by Display Name
   * @return array An array of QBO Employees
   * 
   */
  private function readAllImpl(bool $associateByName = false){

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
          if ($item->EmployeeNumber) {
            if ($associateByName) {
              $employeeArray[$item->DisplayName] = $employee;
            } else {
              $employeeArray[$item->Id] = $employee;
            }
          }
        }

        return $employeeArray;
    }
  }


}