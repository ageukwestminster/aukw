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
    JournalCtl::transferParameters($data, $model);

    $result = $model->create();
    if ($result) {
        echo json_encode(
            array("message" => "Journal '". $result['label']  ."' has been added for " . $result['date'] . ".",
                "id" => $result['id'])
          );
    }
  }

  private static function transferParameters($data, $model)
  {
    // is transaction in balance?
    // Sales + CharityDonations + CashDiscrepency + CashToCharity + CreditCardReceipts + VolExps + StaffExps + ToBank
    $balance = $data->sales + $data->cashDiscrepency + $data->cashToCharity + 
                  $data->creditCards + $data->volunteerExpenses+$data->staffExpenses + $data->cash;
    
    if ($balance >= 0.005) {
      http_response_code(400);  
      echo json_encode(
        array(
          "message" => "Unable to enter daily journal in Quickbooks. Transaction is not in balance.")
          , JSON_NUMERIC_CHECK);
      exit(1);
    }

    $model->date = $data->date;          
    $model->shopid = $data->shopid;          
    $model->donations = $data->donations;
    $model->cashDiscrepency = $data->cashDiscrepency;
    $model->creditCards = $data->creditCards;
    $model->cash = $data->cash;
    $model->staffExpenses = $data->staffExpenses;
    $model->volunteerExpenses = $data->volunteerExpenses;
    $model->sales = $data->sales;
    $model->cashToCharity = $data->cashToCharity;    
  }
}