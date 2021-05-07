<?php

namespace Controllers;

class JournalCtl{


  public static function read_one($id){  

    $model = new \Models\QuickbooksJournal();
    $model->id = $id;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
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
        array("message" => "Already entered into Quickbooks.")
      );
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

    JournalCtl::check_parameters($model);

    $result = $model->create();
    if ($result) {
      $takings->quickbooks = 1;
      $takings->update();
      echo json_encode(
            array("message" => "Journal '". $result['label']  ."' has been added for " . $result['date'] . ".",
                "id" => $result['id'])
          );
    }
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
          "message" => "Unable to enter daily journal in Quickbooks. Transaction is not in balance.")
          , JSON_NUMERIC_CHECK);
      exit(1);
    }

  
  }
}