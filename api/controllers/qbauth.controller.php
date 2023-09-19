<?php

namespace Controllers;

use Models\QuickbooksAuth;

/**
 * Controller that provides methods to create and manage a 
 * connection between the system and QBO.
 * 
 * @category  Controller
 */
class QBAuthCtl{

  /** 
   * Start the OAuth2 process to create a link between QuickBooks and this app
   * @return array The Uri to follow to make the link plus instructions on what to do
   */
  public static function oauth2_begin(){
    $model = new QuickbooksAuth();

    echo json_encode($model->begin(), JSON_NUMERIC_CHECK);
  }

  /**
   * As part of the QBO OAuth2 process the server contacts QBO to make a copnnection to
   * a QBO company. If the user supplies valided credentials then QBO sends back to the
   * system authorization codes. It does this by means of a callback uri. When the QBO
   * API calls the endpoint the system routes the request through here.
   * 
   * @return void Output is echo'd directly to response Output is echo'd directly to response
   */
  public static function oauth2_callback(){
    $model = new QuickbooksAuth();
    $model->callback();
    echo json_encode(
      array("status" => "success",
      "message" => "The connection to Quickbooks is now online. You can close this window.")
    );
  }

  /**
   * Break the link between this app and Quickbooks
   * 
   * @return void Output is echo'd directly to response
   */
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


  /**
   * Refresh the QB access token from the refresh token
   *
   * @return bool 
   * 
   */
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
   * Show details of the authenticated connection with QBO, if it exists.
   * 
   * @return QuickbooksToken Contains the access and refresh tokens for QBO
   */
  public static function connection_details(){  

    $model = new \Models\QuickbooksToken();
    $model->read();

    echo json_encode($model, JSON_NUMERIC_CHECK);
  }


}