<?php

namespace Controllers;

use \Datetime;

class SalesReceiptCtl{

  const NOT_IN_QUICKBOOKS = 0;

  public static function read_one($id){  

    $model = new \Models\SalesReceiptJournal();
    $model->id = $id;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
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

    $model = new \Models\SalesReceiptJournal();
    $data = json_decode(file_get_contents("php://input"));
    $model->date = $data->date;          
    $model->shopid = $data->shopid ?? 1;          
    $model->donations = $data->donations ?? $emptySales;
    $model->cashDiscrepency = $data->cashDiscrepency ?? 0;
    $model->creditCards = $data->creditCards ?? 0;
    $model->cash = $data->cash ?? 0;
    $model->operatingExpenses = $data->operatingExpenses ?? 0;
    $model->volunteerExpenses = $data->volunteerExpenses ?? 0;
    $model->clothing = $data->clothing ?? $emptySales;
    $model->brica = $data->brica ?? $emptySales;
    $model->books = $data->books ?? $emptySales;
    $model->linens = $data->linens ?? $emptySales;
    $model->ragging = $data->ragging ?? $emptySales;
    $model->cashToCharity = $data->cashToCharity ?? 0;  
    $model->privatenote = $data->comments ?? '';  

    $model->sales = $model->clothing->sales + $model->brica->sales + 
              $model->books->sales + $model->linens->sales + $model->ragging->sales;
    if ($model->sales < 0) {
      http_response_code(400);
      echo json_encode(
        array("message" => "This transaction has a negative total amount.")
      );
      exit(0);
    }              

    SalesReceiptCtl::check_parameters($model);

    $result = $model->create();
    if ($result) {
        echo json_encode(
            array("message" => "Sales Receipt '". $result['label']  ."' has been added for " . $result['date'] . ".",
                "id" => $result['id'])
          );
    }
  }

  public static function create_from_takings($takingsid){  

    $model = new \Models\SalesReceiptJournal();

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

    $model->date = $takings->date;          
    $model->shopid = $takings->shopid;          
    $model->clothing = (object) [ 'number' => $takings->clothing_num, 'sales' => $takings->clothing ] ;
    $model->brica = (object) [ 'number' => $takings->brica_num, 'sales' => $takings->brica ] ;
    $model->books = (object) [ 'number' => $takings->books_num, 'sales' => $takings->books ] ;
    $model->linens = (object) [ 'number' => $takings->linens_num, 'sales' => $takings->linens ] ;
    $model->donations = (object) [ 'number' => $takings->donations_num, 'sales' => $takings->donations ] ;
    $model->ragging = (object) [ 'number' => $takings->rag_num, 'sales' => $takings->rag ] ;
    $model->cashDiscrepency = $takings->cash_difference;
    $model->creditCards = $takings->credit_cards*-1;
    $model->cash = $takings->cash_to_bank*-1;
    $model->operatingExpenses = $takings->operating_expenses*-1 + $takings->other_adjustments*-1;
    $model->volunteerExpenses = $takings->volunteer_expenses*-1;
    $model->sales = $takings->clothing + $takings->brica + $takings->books + $takings->linens + $takings->other;
    $model->cashToCharity = $takings->cash_to_charity*-1; 
    $model->privatenote = $takings->comments ?? "";

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

  private static function create_all_from_takings(){  

    $model = new \Models\QuickbooksJournal();

    // search for all the takings objects that are not yet entered into Quickbooks
    $takingsModel = new \Models\Takings();
    $takingsArray = $takingsModel->read_by_quickbooks_status(self::NOT_IN_QUICKBOOKS);

    // Empty array ?
    if ( count($takingsArray) == 0) {
      http_response_code(200);
      echo json_encode(
        array("message" => "No takings available to be entered into Quickbooks. Empty array returned from database.")
      );
      exit(0);
    }

    $message=array();

    foreach ($takingsArray as $takings) {
      $model = new \Models\QuickbooksJournal();
      $model->date = $takings["date"];          
      $model->shopid = $takings["shopid"];          
      $model->donations = floatval($takings["donations"]);
      $model->cashDiscrepency = floatval($takings["cash_difference"]);
      $model->creditCards = $takings["credit_cards"]*-1;
      $model->cash = $takings["cash_to_bank"]*-1;
      $model->operatingExpenses = $takings["operating_expenses"]*-1 + $takings["other_adjustments"]*-1;
      $model->volunteerExpenses = $takings["volunteer_expenses"]*-1;
      $model->sales = floatval($takings["clothing"]) + floatval($takings["brica"]) + floatval($takings["books"])
                           + floatval($takings["linens"]) + floatval($takings["other"]);
      $model->cashToCharity = $takings["cash_to_charity"]*-1;
      $model->privatenote = "Created by automated process at " . \Core\DatesHelper::currentDateTime() . '. ' . $takings["comments"];

      JournalCtl::check_parameters($model);

      $result = $model->create();
      if ($result) {
        $takingsModel->id = $takings["id"];
        $takingsModel->quickbooks = 1;
        $takingsModel->patch_quickbooks();
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
    $model->donations = $takings->donations;
    $model->cashDiscrepency = $takings->cash_difference;
    $model->creditCards = $takings->credit_cards*-1;
    $model->cash = $takings->cash_to_bank*-1;
    $model->operatingExpenses = $takings->operating_expenses*-1 + $takings->other_adjustments*-1;
    $model->volunteerExpenses = $takings->volunteer_expenses*-1;
    $model->sales = $takings->clothing + $takings->brica + $takings->books + $takings->linens + $takings->other;
    $model->cashToCharity = $takings->cash_to_charity*-1;  
  }

  private static function check_parameters($model)
  {
    $tests = array(
      $model->cashDiscrepency,
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
    $balance += $model->cashDiscrepency + $model->cashToCharity + $model->creditCards;
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