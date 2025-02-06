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
        throw new \Exception("The Response message is: " . $error->getResponseBody() . "\n");
      }
      else {
        if (property_exists($item, 'Employee')) {
          /** @disregard Intelephense error on next line */
          return $item->Employee;
        } else {
          return $item;
        }
      }
  }

  /**
   * Return details of all QBO Employees
   * 
   * @return array An array of QBO Employees, associated by QBO Id
   * 
   */
  public function readAll():array{
    return $this->readAllImpl(false);
  }

  /**
   * Return details of all QBO Employees
   * 
   * @return array An array of QBO Employees, associated by Name
   * 
   */
  public function readAllAssociatedByName():array{
    return $this->readAllImpl(true);
  }

  /**
   * Return details of all QBO Employees. However who do not have an EmployeeID asigned to them are excluded from this list.
   * @param bool $associateByName If 'true' return an associative array, sorted by Display Name
   * @return array An array of QBO Employees who have a valid EmployeeID associated with them
   * 
   */
  private function readAllImpl(bool $associateByName = false):array{

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);
    if ($dataService == false) {
      return [];
    }

    $items = $dataService->FindAll('Employee');
    $error = $dataService->getLastError();
    if ($error) {
      throw new \Exception("The Response message is: " . $error->getResponseBody() . "\n");
    }
    else {

        $employeeArray = array();
        foreach ($items as $item) {
          $employee = array(
            "quickbooksId" => $item->Id,
            "name" => $item->DisplayName,
            "payrollNumber" => $item->EmployeeNumber,
            "familyName" => $item->FamilyName??'Unknown'
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