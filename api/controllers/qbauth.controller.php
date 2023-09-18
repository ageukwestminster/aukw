<?php

namespace Controllers;

use Models\QuickbooksAuth;

class QBAuthCtl{

  public static function oauth2_begin(){
    $model = new QuickbooksAuth();

    echo json_encode($model->begin(), JSON_NUMERIC_CHECK);
  }

  public static function oauth2_callback(){
    $model = new QuickbooksAuth();
    $model->callback();
    echo json_encode(
      array("status" => "success",
      "message" => "The connection to Quickbooks is now online. You may close this window.")
    );
  }

  public static function oauth2_revoke(){
    $model = new QuickbooksAuth();
    if ($model->revoke()) {
      echo json_encode(
      array("message" => "Your Quickbooks tokens have been revoked.")
      );
    } else {
      http_response_code(400);
      echo json_encode(
      array("message" => "Unable to revoke Quickbooks tokens.")
    );
  }

  }

  public static function oauth2_refresh(){
    $model = new QuickbooksAuth();
    if ($model->refresh()) {
    echo json_encode(
      array("message" => "Quickbooks Tokens refreshed.")
      );
    } else {
    http_response_code(400);
    echo json_encode(
      array("message" => "Unable to refresh Quickbooks Tokens.")
      );
    }
  }

  /**
   * 
   * Show details of the authenticated connection to QBO, if it exists.
   * 
   * @return QuickbooksToken Contains the access and refresh tokens for QBO
   */
  public static function connection_details(){  

    $model = new \Models\QuickbooksToken();
    $model->read();

    echo json_encode($model, JSON_NUMERIC_CHECK);
  }


}