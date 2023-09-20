<?php

namespace Controllers;

use \Datetime;
use \Models\QuickbooksSalesReceipt;
use \Models\Takings;


/**
 * Controller to accomplish QBO sales receipt related tasks. 
 *
 * @category  Controller
*/
class SalesReceiptCtl{


  /**
   * Return details of the sales receipt identified by $id
   *
   * @param int $id The QBO id, not the DocNumber
   * @return void Output is echoed directly to response 
   */
  public static function read_one(int $id){  

    $model = QuickbooksSalesReceipt::getInstance()->setId($id);

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);   
  }

  /**
   * Delete from QBO the sales receipt identified by $id
   *
   * @param int $id The QBO id, not the DocNumber
   * @return void Output is echoed directly to response 
   */
  public static function delete(int $id){  

    $model = QuickbooksSalesReceipt::getInstance()->setId($id);

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

  // Sales items should be positive, Expenses and cash/credit cards are negative.
  // Example:
  // { "date": "2022-04-29", "donations": { "number": 0, "sales": 0 }, 
  //   "cashDiscrepency": 0.05,"creditCards": -381.2,"cash": -183.30,
  //   "operatingExpenses": -1.3,"volunteerExpenses": -5,
  //   "clothing": { "number": 53, "sales": 310.50 },
  //   "brica": { "number": 75, "sales": 251.75 },
  //   "books": { "number": 4, "sales": 3.5 },
  //   "linens": { "number": 1, "sales": 5 },
  //   "cashToCharity": 0, "shopid": 1
  //  }
  public static function create(){  

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
        ->setPrivateNote($data->comments ?? ''
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
          "message" => "Unable to enter daily journal in Quickbooks. Transaction is not in balance for '" .
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

  public static function create_from_takings($takingsid){  

    $takings = new \Models\Takings();
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

    $model = new \Models\QuickbooksSalesReceipt();
    SalesReceiptCtl::transfer_parameters($model, $takings);
    SalesReceiptCtl::check_parameters($model);

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

  public static function patch(){
    $data = json_decode(file_get_contents("php://input"));
    if(isset($data->method)){

      switch (strtolower($data->method)) {
        case 'create_all':
          SalesReceiptCtl::create_all_from_takings();
            break;
        default:
        http_response_code(422);  
        echo json_encode(
          array(
            "message" => "Unknown method",
            "method" => $data->method
          )
        );

      }
    }
  }
  
  private static function create_all_from_takings(){  

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
      
      $model = new QuickbooksSalesReceipt();

      SalesReceiptCtl::transfer_parameters($model, $takings);
      SalesReceiptCtl::check_parameters($model);

      $result = $model->create();
      if ($result) {
        $takings->quickbooks = 1;
        $takings->patch_quickbooks();
        $message[] = array("message" => "Journal '". $result['label']  
                    ."' has been added for " . $result['date'] 
                    . ".", "id" => $result['id']);
      }
    }

    echo json_encode($message);

  }

  private static function transfer_parameters($model, $takings) {
    $model->date = $takings->date;          
    $model->shopid = $takings->shopid;          
    $model->clothing = (object) [ 'number' => $takings->clothing_num, 'sales' => $takings->clothing ] ;
    $model->brica = (object) [ 'number' => $takings->brica_num, 'sales' => $takings->brica ] ;
    $model->books = (object) [ 'number' => $takings->books_num, 'sales' => $takings->books ] ;
    $model->linens = (object) [ 'number' => $takings->linens_num, 'sales' => $takings->linens ] ;
    $model->donations = (object) [ 'number' => $takings->donations_num, 'sales' => $takings->donations ] ;
    $model->ragging = (object) [ 'number' => $takings->rag_num, 'sales' => $takings->rag ] ;
    $model->cashDiscrepancy = $takings->cash_difference;
    $model->creditCards = $takings->credit_cards*-1;
    $model->cash = $takings->cash_to_bank*-1;
    $model->operatingExpenses = $takings->operating_expenses*-1 + $takings->other_adjustments*-1;
    $model->volunteerExpenses = $takings->volunteer_expenses*-1;
    $model->sales = $takings->clothing + $takings->brica + $takings->books + $takings->linens + $takings->other;
    $model->cashToCharity = $takings->cash_to_charity*-1; 
    $model->privatenote = $takings->comments ?? "";  
  }

  private static function check_parameters($model)
  {
    $tests = array(
      $model->cashDiscrepancy,
      $model->cashToCharity,
      $model->creditCards,
      $model->volunteerExpenses,
      $model->operatingExpenses,
      $model->cash,
      $model->clothing->number,
      $model->clothing->sales,
      $model->brica->number,
      $model->brica->sales,
      $model->books->number,
      $model->books->sales,
      $model->linens->number,
      $model->linens->sales,
      $model->donations->number,
      $model->donations->sales,
      $model->ragging->number,
      $model->ragging->sales,
    );
    foreach ($tests as $element) {
      if (!is_numeric($element)) {
        http_response_code(400);  
        echo json_encode(
          array(
            "message" => "Unable to enter daily sales receipt in Quickbooks. " . var_export($element, true) . " is NOT numeric.")
            , JSON_NUMERIC_CHECK);
        exit(1);
      }
    }

    // is transaction in balance?
    $balance = $model->donations->sales + $model->clothing->sales + $model->brica->sales;
    $balance += $model->books->sales + $model->linens->sales + $model->ragging->sales;
    $balance += $model->cashDiscrepancy + $model->cashToCharity + $model->creditCards;
    $balance += $model->volunteerExpenses + $model->operatingExpenses + $model->cash;
    
    if (abs($balance) >= 0.005) {
      http_response_code(400);  
      echo json_encode(
        array(
          "message" => "Unable to enter daily journal in Quickbooks. Transaction is not in balance for '" .
          $model->date . "'.")
          , JSON_NUMERIC_CHECK);
      exit(1);
    }

  
  }
}