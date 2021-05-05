<?php

namespace Controllers;

class TakingsCtl{

  public static function read_one($id){  

    $model = new \Models\Takings();
    $model->id = $id;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

  public static function create(){

    $model = new \Models\Takings();
    $data = json_decode(file_get_contents("php://input"));
    TakingsCtl::transferParameters($data, $model);
    
    // INSERT the row into the database
    if( $model->create()) {
      echo json_encode(
        array(
          "message" => "New takings with id=$model->id was created.",
          "id" => $model->id
        )
      , JSON_NUMERIC_CHECK);
    } else{
      // if unable to create the model, tell the admin
        http_response_code(400);  
        echo json_encode(
          array("message" => "Unable to INSERT row.")
        );
    }
  }

  public static function update($id){

    $model = new \Models\Takings();
    $model->id = $id;

    $data = json_decode(file_get_contents("php://input"));
    TakingsCtl::transferParameters($data, $model);

    if($model->update()){
        echo json_encode(
            array(
                "message" => "Takings with id=$model->id was updated.",
                "id" => $model->id
            )
            , JSON_NUMERIC_CHECK);
    }
    else{
        http_response_code(400); 
        echo json_encode(
            array("message" => "Unable to UPDATE takings.")
        );
    }
  }

  public static function delete($id){  

    $model = new \Models\Takings();
    $model->id = $id;

    if( $model->delete()) {
        echo json_encode(
          array(
            "message" => "Takings with id=$model->id was deleted.",
            "id" => $model->id)
            , JSON_NUMERIC_CHECK);
      } else{
          http_response_code(400);  
          echo json_encode(
            array(
              "message" => "Unable to DELETE row.",
              "id" => $model->id)
              , JSON_NUMERIC_CHECK);
      }
  }

  private static function transferParameters($data, $model)
  {
    if (isset($data->date)) {
        $model->date = $data->date;          
    } else {
        $returnValue= "Takings 'date' missing";
    }
    if (isset($data->shopid)) {
        $model->shopid = $data->shopid;          
    } else {
        $returnValue= "Takings 'shopid' missing";
    }
    $model->clothing_num = $data->clothing_num;
    $model->brica_num = $data->brica_num;
    $model->books_num = $data->books_num;
    $model->linens_num = $data->linens_num;
    $model->donations_num = $data->donations_num;
    $model->other_num = $data->other_num;
    $model->rag_num = $data->rag_num;
    $model->clothing = $data->clothing;
    $model->brica = $data->brica;
    $model->books = $data->books;
    $model->linens = $data->linens;
    $model->donations = $data->donations;
    $model->other = $data->other;
    $model->rag = $data->rag;
    $model->customers_num_total = $data->customers_num_total;
    $model->cash_to_bank = $data->cash_to_bank;
    $model->credit_cards = $data->credit_cards;
    $model->operating_expenses = $data->operating_expenses;
    $model->volunteer_expenses = $data->volunteer_expenses;
    $model->other_adjustments = $data->other_adjustments;
    $model->cash_to_charity = $data->cash_to_charity;
    $model->cash_difference = $data->cash_difference;
    $model->comments = $data->comments;
    $model->rags_paid_in_cash = $data->rags_paid_in_cash;
    $model->quickbooks = empty($data->quickbooks)?0:$data->quickbooks;
    $model->rags_paid_in_cash = empty($data->rags_paid_in_cash)?0:$data->rags_paid_in_cash;

  }
}