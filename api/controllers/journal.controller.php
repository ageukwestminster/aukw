<?php

namespace Controllers;

use \Datetime;

class JournalCtl{

  const NOT_IN_QUICKBOOKS = 0;

  public static function read_one($id){  

    $model = new \Models\QuickbooksJournal();
    $model->id = $id;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

  public static function patch(){
    $data = json_decode(file_get_contents("php://input"));
    if(isset($data->method)){

      switch (strtolower($data->method)) {
        case 'create_all':
          JournalCtl::create_all_from_takings();
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

  public static function create(){  

    $model = new \Models\QuickbooksJournal();
    $data = json_decode(file_get_contents("php://input"));
    $model->date = $data->date;          
    $model->shopid = $data->shopid;          
    $model->donations = $data->donations;
    $model->cashDiscrepency = $data->cashDiscrepency;
    $model->creditCards = $data->creditCards;
    $model->cash = $data->cash;
    $model->operatingExpenses = $data->operatingExpenses;
    $model->volunteerExpenses = $data->volunteerExpenses;
    $model->sales = $data->sales;
    $model->cashToCharity = $data->cashToCharity;  

    JournalCtl::check_parameters($model);

    $result = $model->create();
    if ($result) {
        echo json_encode(
            array("message" => "Journal '". $result['label']  ."' has been added for " . $result['date'] . ".",
                "id" => $result['id'])
          );
    }
  }

  public static function create_from_takings($takingsid){  

    $model = new \Models\QuickbooksJournal();

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
    $model->donations = $takings->donations;
    $model->cashDiscrepency = $takings->cash_difference;
    $model->creditCards = $takings->credit_cards*-1;
    $model->cash = $takings->cash_to_bank*-1;
    $model->operatingExpenses = $takings->operating_expenses*-1 + $takings->other_adjustments*-1;
    $model->volunteerExpenses = $takings->volunteer_expenses*-1;
    $model->sales = $takings->clothing + $takings->brica + $takings->books + $takings->linens + $takings->other;
    $model->cashToCharity = $takings->cash_to_charity*-1; 
    $model->privatenote = "Created at " . \Core\DatesHelper::currentDateTime() . '. ' . $takings->comments;

    JournalCtl::check_parameters($model);

    $result = $model->create();
    if ($result) {
      $takings->quickbooks = 1;
      $takings->patch_quickbooks();
      echo json_encode(
            array("message" => "Journal '". $result['label']  ."' has been added for " . $result['date'] . ".",
                "id" => $result['id'])
          );
    }
  }

  private static function create_all_from_takings(){  

    $model = new \Models\QuickbooksJournal();

    // search for al lthe takings objects that are not yet entered into Quickbooks
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
      $model->donations,
      $model->sales,
      $model->cashDiscrepency,
      $model->cashToCharity,
      $model->creditCards,
      $model->volunteerExpenses,
      $model->operatingExpenses,
      $model->cash
    );
    foreach ($tests as $element) {
      if (!is_numeric($element)) {
        http_response_code(400);  
        echo json_encode(
          array(
            "message" => "Unable to enter daily journal in Quickbooks. " . var_export($element, true) . " is NOT numeric.")
            , JSON_NUMERIC_CHECK);
        exit(1);
      }
    }

    // is transaction in balance?
    $balance = $model->donations + $model->sales + $model->cashDiscrepency + $model->cashToCharity + 
                  $model->creditCards + $model->volunteerExpenses+$model->operatingExpenses + $model->cash;
    
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