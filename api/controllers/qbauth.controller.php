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
   * @return void Output is echo'd directly to response
   */
  public static function oauth2_callback(){

    $code = $_GET['code'];        
    $realmId = $_GET['realmId'];
    $state = $_GET['state'];

    if (empty($code) || empty($realmId) || empty($state) ) {
        http_response_code(400);  
        echo json_encode(
          array("message" => "Unable to proceed with QB callback: provided parameters not as expected")
        );
        exit(0);
    }

    $model = new QuickbooksAuth();
    $model->callback($code, $realmId, $state);
  }

  /**
   * Break the link between this app and Quickbooks
   * @param int $userid The database id of the user whose token is being revoked
   * @return void Output is echo'd directly to response
   */
  public static function oauth2_revoke($userid){
    $model = new QuickbooksAuth();

    if(!isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a value for the 'realmid' parameter.")
      );
      exit(1);
    } 

    $realmid = $_GET['realmid'];

    if ($model->revoke($userid, $realmid)) {
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
   * @param int $userid The database id of the user whose token is being refreshed
   * @return void 
   * 
   */
  public static function oauth2_refresh($userid){
    $model = new QuickbooksAuth();

    if(!isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
          array("message" => "Please supply a value for the 'realmid' parameter.")
      );
      exit(1);
    } 

    $realmid = $_GET['realmid'];

    if ($model->refresh($userid, $realmid)) {
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
   * Show details of authenticated connections with QBO for a given user.
   * 
   * @param int $userid The database id of the user whose connections are being sought
   * @return QuickbooksToken[] Containing the access and refresh tokens for QBO
   */
  public static function connection_details($userid){  

    $model = new \Models\QuickbooksToken();

    if( !isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
          array("message" => "Please supply realmid as a parameter.")
      );
      exit(1);
    } 

    $realmid = $_GET['realmid'];

    if (!is_numeric($realmid)) {
      http_response_code(400);   
      echo json_encode(
          array("message" => "Expecting a numeric format for the parameter realmid.")
      );
      exit(1);
    }
    
    $model->read($userid, $realmid);

    if ($model->accesstoken) {
      echo json_encode($model, JSON_NUMERIC_CHECK);
    } else {
      $model = new \stdClass();
      echo json_encode($model, JSON_NUMERIC_CHECK);
    }

  }

  /**
   * 
   * Show details of all the authenticated connections with QBO for a given user.
   * @param int The Id of the user
   * 
   * @return QuickbooksToken[] Containing the access and refresh tokens for QBO
   */
  public static function all_connection_details($userid){  

    $model = new \Models\QuickbooksToken();

    echo json_encode($model->read_all($userid), JSON_NUMERIC_CHECK);
  }

}