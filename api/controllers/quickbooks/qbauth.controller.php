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
   * As part of the QBO OAuth2 process the server contacts QBO to make a connection to
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
   * Break the link between this app and a Quickbooks Company.
   * @param string $realmid The id of the QBO company.
   * @return void Output is echo'd directly to response.
   */
  public static function oauth2_revoke(string $realmid) : void{
    $model = new QuickbooksAuth();

    if ($model->revoke($realmid)) {
      echo json_encode(
      array(
        "message" => "Your QuickBooks token has been revoked.",
        "id" => $realmid
        ));
    } else {
      http_response_code(400);
      echo json_encode(
      array("message" => "Unable to revoke Quickbooks token.",
      "id" => $realmid)
    );
  }

  }


  /**
   * Refresh the QB access token from the refresh token, for the given QB Company.
   * @param string $realmid The id of the QBO company.
   * @param string $userid The database id of the user whose token is being refreshed.
   * @return void Output is echo'd directly to response.
   * 
   */
  public static function oauth2_refresh(string $realmid, string $userid) : void{
    $model = new QuickbooksAuth();

    if ($model->refresh($realmid, $userid)) {
    echo json_encode(
      array("message" => "Quickbooks Tokens refreshed.",
      "id" => $realmid)
      );
    } else {
    http_response_code(400);
    echo json_encode(
      array("message" => "Unable to refresh Quickbooks Tokens.",
      "id" => $realmid)
      );
    }
  }

  /**
   * Show details of authenticated connections with QBO for a given user.
   * @param string $realmid The id of the QBO company.
   * @param string $userid The database id of the user whose connections are being sought
   * @return QuickbooksToken[] Containing the access and refresh tokens for QBO
   */
  public static function connection_details(string $realmid){  

    $model = new \Models\QuickbooksToken();
    
    $model->read($realmid);

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
  public static function all_connection_details(){  

    $model = new \Models\QuickbooksToken();

    echo json_encode($model->read_all(), JSON_NUMERIC_CHECK);
  }

}