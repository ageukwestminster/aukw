<?php

namespace Controllers;

use Exception;

use Core\ErrorResponse as Error;
use Core\QuickbooksConstants as QBO;
use Models\Allocations;
use Models\Allocation;

/**
 * Controller to acomplish Allocations related CRUD tasks
 * Allocations are assignments of portions of employee salaries to projects (aka QBO classes)
 *
 * @category  Controller
*/
class AllocationsCtl{

  /**
   * Return details of all Allocations
   * 
   * @return void Output is echo'd directly to response 
   */
  public static function read_all():void{  
    try {
      // Note alsence of JSON_NUMERIC_CHECK to preserve class as string, despite being numeric
      echo json_encode(Allocations::getInstance()->read());
    } catch (Exception $e) {
      Error::response("Error retrieving details of all Allocations.", $e);
    }
  }

  /**
   * Return details of one Allocation
   * 
   * @return void Output is echo'd directly to response 
   */
  public static function read_one(int $quickbooksId, string $class):void{  
    try {
      // Note alsence of JSON_NUMERIC_CHECK to preserve class as string, despite being numeric
      echo json_encode(
        Allocation::getInstance()
        ->setQuickbooksId($quickbooksId)
        ->setClass($class)
        ->readOne()
      );
    } catch (Exception $e) {
      Error::response("Error retrieving details of one Allocation.", $e);
    }
  }


  /**
   * Delete from the database all the Allocations
   * @return void Output is echo'd directly to response
   * 
   */
  public static function delete():void{
    try {
      if( Allocations::getInstance()->delete()) {
        echo json_encode(
          array(
            "message" => "Allocations table has been cleared.",
          )
        , JSON_NUMERIC_CHECK);
      } else{
        throw new Exception("Deleting allocations failed for unknown reason.");
      }
    } catch (Exception $e) {
      Error::response("Error deleting allocations.", $e);
    }
  }

    /**
   * Delete from the database all the Allocations
   * @return void Output is echo'd directly to response
   * 
   */
  public static function restore(?int $versionID = null):void{
    try {
      if( Allocations::getInstance()->restore($versionID)) {
        echo json_encode(
          array(
            "message" => "Allocations table has been restored.",
          )
        , JSON_NUMERIC_CHECK);
      } else{
        throw new Exception("Restoring allocations failed for unknown reason.");
      }
    } catch (Exception $e) {
      Error::response("Error restoring allocations.", $e);
    }
  }

  /**
   * Add a new set of allocations to the database.
   * 
   * @return void Output is echo'd directly to response
   * 
   */
  public static function create():void{
    try {
      
      if (Allocations::getInstance()->delete() === false) {
        throw new Exception("Failed to clear existing allocations before insert.");
      }

      $data = json_decode(file_get_contents("php://input"));
      $model = Allocation::getInstance();

      foreach($data as $item){
        // Account can be omitted in the input, so determine it here
        $account = isset($item->account) ? 
                      $item->account : 
                      QBO::payrollAccountFromEmployeeStatus($item->isShopEmployee);

        $model
          ->setQuickbooksId($item->quickbooksId)
          ->setPayrollNumber($item->payrollNumber)
          ->setPercentage($item->percentage)
          ->setAccount($account)
          ->setClass($item->class)
          ->setIsShopEmployee($item->isShopEmployee);

        if ($model->create() !== true) {
          throw new Exception("Failed to insert new allocation for payroll number " . $item->payrollNumber);
        }
      }

      echo json_encode(
        array(
          "message" => "New allocations have been created.",
        )
      , JSON_NUMERIC_CHECK);

    } catch (Exception $e) {
      Error::response("Error inserting new Allocations.", $e);
    }
  } 
  
    /**
   * Add a single new allocation to the database.
   * 
   * @return void Output is echo'd directly to response
   * 
   */
  public static function append():void{
    try {

      if (!Allocations::getInstance()->verify()) {
        throw new Exception("Percentage totals do not equal 100% before append, aborting.");
      }

      $data = json_decode(file_get_contents("php://input"));

      foreach($data as $item){

        $model = Allocation::getInstance();

        $allocation = $model
          ->setQuickbooksId($item->quickbooksId)
          ->setClass($item->class)
          ->readOne();
        
        // Account can be omitted in the input, so determine it here
        $account = isset($item->account) ? 
                      $item->account : 
                      QBO::payrollAccountFromEmployeeStatus($item->isShopEmployee);

        $model
          ->setPayrollNumber($item->payrollNumber)
          ->setPercentage($item->percentage)
          ->setAccount($account)
          ->setClass($item->class)
          ->setIsShopEmployee($item->isShopEmployee);
        if ($allocation !== null) {
          $result = $model->update();
        } else {
          $result = $model->create();
        }   

        if ($result !== true) {
          throw new Exception("Failed to insert new allocation for payroll number " . $item->payrollNumber);
        }
      }

      if (!Allocations::getInstance()->verify()) {
        throw new Exception("Percentage totals do not equal 100% after append.");
      }

      echo json_encode(
        array(
          "message" => "Allocation(s) have been appended.",
        )
      , JSON_NUMERIC_CHECK);

    } catch (Exception $e) {
      Error::response("Error appending new Allocation(s).", $e);
    }
  } 

  private static function accountNumberFromId(bool $isShopEmployee):int {
    if ($isShopEmployee) {
      return QBO::AUEW_ACCOUNT;
    } else {
      return QBO::EMPLOYER_NI_ACCOUNT;
    }

  }
}