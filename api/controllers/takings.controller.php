<?php

namespace Controllers;

class TakingsCtl{

  public static function read_one($id){  

    $model = new \Models\Takings();
    $model->id = $id;

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

  public static function read_by_quickbooks_status($quickbooks){  

    $model = new \Models\Takings();

    echo json_encode($model->read_by_quickbooks_status($quickbooks), JSON_NUMERIC_CHECK);
  }

  public static function read_by_shop($shopid){  

    $model = new \Models\Takings();

    echo json_encode($model->read_by_shop($shopid), JSON_NUMERIC_CHECK);
  }

  public static function read_most_recent($shopid){  

    $model = new \Models\Takings();

    echo json_encode($model->read_most_recent($shopid), JSON_NUMERIC_CHECK);
  }

  public static function summary($shopid){  

    $model = new \Models\Takings();

    echo json_encode($model->summary($shopid), JSON_NUMERIC_CHECK);
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

  public static function patch($id){
    $data = json_decode(file_get_contents("php://input"));
    if(isset($data->quickbooks)){

      $model = new \Models\Takings();
      $model->id = $id;
      $model->quickbooks = empty($data->quickbooks)?0:$data->quickbooks;

      if ($model->patch_quickbooks()) {
        echo json_encode(
          array(
            "message" => "Takings with id=$model->id was patched to set Quickbooks to "
            . $model->quickbooks .".",
            "id" => $model->id)
            , JSON_NUMERIC_CHECK);
      } else {
        http_response_code(400);  
        echo json_encode(
          array(
            "message" => "Unable to PATCH takings row.",
            "id" => $model->id,
            "quickbooks" => $data->quickbooks)
            , JSON_NUMERIC_CHECK);
      }            
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
    $model->clothing_num = empty($data->clothing_num)?0:$data->clothing_num;
    $model->brica_num = empty($data->brica_num)?0:$data->brica_num;
    $model->books_num = empty($data->books_num)?0:$data->books_num;
    $model->linens_num = empty($data->linens_num)?0:$data->linens_num;
    $model->donations_num = empty($data->donations_num)?0:$data->donations_num;
    $model->other_num = empty($data->other_num)?0:$data->other_num;
    $model->rag_num = empty($data->rag_num)?0:$data->rag_num;
    $model->clothing = empty($data->clothing)?0:$data->clothing;
    $model->brica = empty($data->brica)?0:$data->brica;
    $model->books = empty($data->books)?0:$data->books;
    $model->linens = empty($data->linens)?0:$data->linens;
    $model->donations = empty($data->donations)?0:$data->donations;
    $model->other = empty($data->other)?0:$data->other;
    $model->rag = empty($data->rag)?0:$data->rag;
    $model->customers_num_total = empty($data->customers_num_total)?0:$data->customers_num_total;
    $model->cash_to_bank = empty($data->cash_to_bank)?0:$data->cash_to_bank;
    $model->credit_cards = empty($data->credit_cards)?0:$data->credit_cards;
    $model->operating_expenses = empty($data->operating_expenses)?0:$data->operating_expenses;
    $model->volunteer_expenses = empty($data->volunteer_expenses)?0:$data->volunteer_expenses;
    $model->other_adjustments = empty($data->other_adjustments)?0:$data->other_adjustments;
    $model->cash_to_charity = empty($data->cash_to_charity)?0:$data->cash_to_charity;
    $model->cash_difference = empty($data->cash_difference)?0:$data->cash_difference;
    $model->comments = $data->comments;
    $model->quickbooks = empty($data->quickbooks)?0:$data->quickbooks;
    $model->rags_paid_in_cash = empty($data->rags_paid_in_cash)?0:$data->rags_paid_in_cash;

  }
}