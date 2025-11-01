<?php

namespace Controllers\QuickBooks;

use Models\QuickbooksEmployee;
use Core\QuickbooksConstants as QBO;
use Core\ErrorResponse as Error;
use Exception;

/**
 * Controller to accomplish QBO Employee related tasks. 
 *
 * @category  Controller
*/
class QBEmployeeCtl{

  /**
   * Return details of the QBEmployee identified by $id
   * @param string $realmid The company ID for the QBO company.
   * @param int $id
   * @return void Output is echo'd directly to response 
   */
  public static function read_one(string $realmid, int $id){  
    try {
      $model = QuickbooksEmployee::getInstance()
        ->setId($id)
        ->setRealmID($realmid);   

      echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
    } catch (\Exception $e) {
      Error::response("Unable to find employee with id=$id in QuickBooks.", $e);
    }
  }

  /**
   * Return details of all QBO Employees. However who do not have an 
   * EmployeeID asigned to them are excluded from this list.
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function read_all(string $realmid){  

    try {
      $model = QuickbooksEmployee::getInstance()
        ->setRealmID($realmid); 

      echo json_encode(array_values($model->readAll()), JSON_NUMERIC_CHECK);

    } catch (\Exception $e) {
      Error::response("Unable to obtain list of employees from QuickBooks.", $e);
    }
  }


  /**
   * Create a QBO employee from data supplied via http POST. The POST body must
   * be a JSON object with the following properties:
   * - givenName: The employee's first name
   * - familyName: The employee's surname
   * - employeeNumber: The employee number used in Payroll to link to Iris
   * 
   * Note: QB Employees cannot be deleted. They can only be made inactive, and that
   * only through the QBO web interface.
   *
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echoed directly to response 
   * 
   */
  public static function create(string $realmid){  
    
    try{

      $data = json_decode(file_get_contents("php://input"));

      if (!isset($data->givenName)) {
        throw new \InvalidArgumentException("'givenName' property is missing from POST body.");
      } else if (!isset($data->familyName)) {
        throw new \InvalidArgumentException("'familyName' property is missing from POST body.");
      } else if (!isset($data->employeeNumber)) {
        throw new \InvalidArgumentException("'employeeNumber' property is missing from POST body.");
      }
      /** @var IPPIntuitEntity $result */
      $result = QuickbooksEmployee::getInstance()
        ->setRealmID($realmid)
        ->setGivenName($data->givenName)
        ->setFamilyName($data->familyName)
        ->setEmployeeNumber($data->employeeNumber)
        ->create();

      if ($result) {
          echo json_encode(
              array("message" => "Employee has been added with Payroll Number " . $data->employeeNumber . ".",
                  "id" => $result->Id)
            );
      }

    } catch (\Exception $e) {
      Error::response("Unable to create QB Employee.", $e);
    }    
  }
}