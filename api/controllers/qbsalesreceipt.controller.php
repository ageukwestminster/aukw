<?php

namespace Controllers;

use \Models\QuickbooksSalesReceipt;
use \Models\Takings;


/**
 * Controller to accomplish QBO sales receipt related tasks. 
 *
 * @category  Controller
*/
class QBSalesReceiptCtl{


  /**
   * Return details of the sales receipt identified by $id
   *
   * @param int $id The QBO id, not the DocNumber
   * @return void Output is echoed directly to response 
   */
  public static function read_one(int $id){  

    if(!isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a value for the 'realmid' parameter.")
      );
      exit(1);
    } 

    $model = QuickbooksSalesReceipt::getInstance()
                ->setId($id)
                ->setRealmID(($_GET['realmid']));

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);   
  }

  /**
   * Delete from QBO the sales receipt identified by $id
   *
   * @param int $id The QBO id, not the DocNumber
   * @return void Output is echoed directly to response 
   */
  public static function delete(int $id){  
    
    if(!isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a value for the 'realmid' parameter.")
      );
      exit(1);
    } 

    $model = QuickbooksSalesReceipt::getInstance()
        ->setId($id)
        ->setRealmID($_GET['realmid']);

    if($model->delete()) {
      echo json_encode(
        array(
          "message" => "Takings with id=$id was deleted.",
          "id" => $id)
          , JSON_NUMERIC_CHECK);
    } else{
        http_response_code(400);  
        echo json_encode(
          array(
            "message" => "Unable to DELETE row.",
            "id" => $id)
            , JSON_NUMERIC_CHECK);
    }
  }

  /**
   * Create a QBO sales receipt from data supplied via http POST
   * Sales items should be positive, Expenses and cash/credit cards are negative.
   * 
   * Sample data:
   *  { "date": "2022-04-29", "donations": { "number": 0, "sales": 0 }, 
   *   "cashDiscrepency": 0.05,"creditCards": -381.2,"cash": -183.30,
   *   "operatingExpenses": -1.3,"volunteerExpenses": -5,
   *   "clothing": { "number": 53, "sales": 310.50 },
   *   "brica": { "number": 75, "sales": 251.75 },
   *   "books": { "number": 4, "sales": 3.5 },
   *   "linens": { "number": 1, "sales": 5 },
   *   "cashToCharity": 0, "shopid": 1
   *  }
   *
   * @return void Output is echoed directly to response 
   * 
   */
  public static function create(){  
    if(!isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a value for the 'realmid' parameter.")
      );
      exit(1);
    } 

    $emptySales = (object) [ 'number' => 0, 'sales' => 0];

    $data = json_decode(file_get_contents("php://input"));

    try {
      $model = QuickbooksSalesReceipt::getInstance()
        ->setDate($data->date)
        ->setShopid($data->shopid ?? 1)
        ->setClothing($data->clothing ?? $emptySales)
        ->setBrica($data->brica ?? $emptySales)
        ->setBooks($data->books ?? $emptySales)
        ->setLinens($data->linens ?? $emptySales)
        ->setRagging($data->ragging ?? $emptySales)
        ->setDonations($data->donations ?? $emptySales)
        ->setCashDiscrepancy($data->cashDiscrepancy ?? 0)
        ->setCreditCards($data->creditCards ?? 0)
        ->setCash($data->cash ?? 0)
        ->setOperatingExpenses($data->operatingExpenses ?? 0)
        ->setVolunteerExpenses($data->volunteerExpenses ?? 0)
        ->setCashToCharity($data->cashToCharity ?? 0)
        ->setPrivateNote($data->comments ?? '')
        ->setRealmID($_GET['realmid']
      );
    } catch (\TypeError $e) {
      http_response_code(422);  
      echo json_encode(
        array(
          "message" => "Unable to enter daily sales receipt in Quickbooks. ",
          "extra" => $e->getMessage()
           )
          , JSON_NUMERIC_CHECK);
      exit(1);
    } catch (\Exception $e) {
    http_response_code(400);  
    echo json_encode(
      array(
        "message" => "Unable to enter daily sales receipt in Quickbooks. ",
        "extra" => $e->getMessage()
         )
        , JSON_NUMERIC_CHECK);
    exit(1);
  }

    if (!$model->validate()) {
      http_response_code(400);  
      echo json_encode(
        array(
          "message" => "Unable to enter sales receipt in Quickbooks. Transaction is not in balance for '" .
          $data->date . "'.")
          , JSON_NUMERIC_CHECK);
      exit(1);      
    }

    $result = $model->create();
    if ($result) {
        echo json_encode(
            array("message" => "Sales Receipt '". $result['label']  ."' has been added for " . $result['date'] . ".",
                "id" => $result['id'])
          );
    }
  }

  /**
   * Create a QB sales receipt from a Takings referenced by the given ID.
   * if the QB object is successfully created then update 
   *
   * @param int $takingsid The id of the Takings
   * 
   * @return void Output is echoed directly to response 
   * 
   */
  public static function create_from_takings(int $takingsid){  

    if(!isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a value for the 'realmid' parameter.")
      );
      exit(1);
    } 

    $takings = new Takings();
    $takings->id = $takingsid;
    $takings->readOne();

    if ($takings->quickbooks != 0) {
      http_response_code(400);
      echo json_encode(
        array("message" => "ID " . $takingsid ." already entered into Quickbooks.")
      );
      exit(0);
    } else if ($takings->date == null) {
      http_response_code(400);
      echo json_encode(
        array("message" => "No takings found in MySQL database with that id (" . $takingsid .").")
      );
      exit(0);
    } else if ($takings->id == 0) {
      exit(0);
    }

    $model=QBSalesReceiptCtl::transfer_takings_data($takings);
    $model->setRealmID($_GET['realmid']);
    $result = $model->create();
    if ($result) {
      $takings->quickbooks = 1;
      $takings->patch_quickbooks();
      echo json_encode(
            array("message" => "Sales Receipt '". $result['label']  ."' has been added for " . $result['date'] . ".",
                "id" => $result['id'])
          );
    }
  }

  /**
   * Create a QBO Sales receipt for each Takings item that has Quickbooks = 0
   *
   * @return void Output is echoed directly to response 
   * 
   */
  public static function create_all_from_takings(){  

    // search for all the takings objects that are not yet entered into Quickbooks
    $takingsModel = new \Models\Takings();
    $takingsArray = $takingsModel->read_by_quickbooks_status(false);

    // Empty array ?
    if ( count($takingsArray) == 0) {
      http_response_code(200);
      echo json_encode(
        array("message" => "No takings available to be entered into Quickbooks. Empty array returned from database.")
      );
      exit(0);
    }

    $message=array();

    foreach ($takingsArray as $takingsRow) {

      $takings = new Takings();
      $takings->id = $takingsRow["id"];
      $takings->readOne();
      
      $model=QBSalesReceiptCtl::transfer_takings_data($takings);

      // TODO: USe QBO Batch https://intuit.github.io/QuickBooks-V3-PHP-SDK/quickstart.html#batch-request

      $result = $model->create();
      if ($result) {
        $takings->quickbooks = 1;
        $takings->patch_quickbooks();
        $message[] = array("message" => "Sales Receipt '". $result['label']  
                    ."' has been added for " . $result['date'] 
                    . ".", "id" => $result['id']);
      }
    }

    echo json_encode($message);

  }

  /**
   * Prepare a sales receipt for insertion by transferring data from the given Takings object.
   *
   * @param Takings $takings
   * 
   * @return QuickbooksSalesReceipt
   * 
   */
  private static function transfer_takings_data($takings) : QuickbooksSalesReceipt{
    try {
      $model = QuickbooksSalesReceipt::getInstance()
        ->setDate($takings->date)
        ->setShopid($takings->shopid ?? 1)
        ->setClothing((object) [ 'number' => $takings->clothing_num, 'sales' => $takings->clothing ])
        ->setBrica((object) [ 'number' => $takings->brica_num, 'sales' => $takings->brica ])
        ->setBooks((object) [ 'number' => $takings->books_num, 'sales' => $takings->books ])
        ->setLinens((object) [ 'number' => $takings->linens_num, 'sales' => $takings->linens ])
        ->setRagging((object) [ 'number' => $takings->rag_num, 'sales' => $takings->rag ])
        ->setDonations((object) [ 'number' => $takings->donations_num, 'sales' => $takings->donations ])
        ->setCashDiscrepancy($takings->cash_difference)
        ->setCreditCards($takings->credit_cards*-1)
        ->setCash($takings->cash_to_bank*-1)
        ->setOperatingExpenses($takings->operating_expenses*-1 + $takings->other_adjustments*-1)
        ->setVolunteerExpenses($takings->volunteer_expenses*-1)
        ->setCashToCharity($takings->cash_to_charity*-1)
        ->setPrivateNote($takings->comments ?? ''
      );
    } catch (\TypeError $e) {
      http_response_code(422);  
      echo json_encode(
        array(
          "message" => "Unable to enter daily sales receipt in Quickbooks. ",
          "extra" => $e->getMessage()
          )
          , JSON_NUMERIC_CHECK);
      exit(1);
    } catch (\Exception $e) {
      http_response_code(400);  
      echo json_encode(
        array(
          "message" => "Unable to enter daily sales receipt in Quickbooks. ",
          "extra" => $e->getMessage()
          )
          , JSON_NUMERIC_CHECK);
      exit(1);
    }

    if (!$model->validate()) {
      http_response_code(400);  
      echo json_encode(
        array(
          "message" => "Unable to enter sales receipt in Quickbooks. Transaction is not in balance for '" .
          $takings->date . "'.")
          , JSON_NUMERIC_CHECK);
      exit(1);      
    }

    return $model;
  }

}