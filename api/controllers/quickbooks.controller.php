<?php

namespace Controllers;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use QuickBooksOnline\API\Facades\JournalEntry;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;

use Models\JWTWrapper;
use Models\QuickbooksAuth;
use Models\QuickbooksToken;
use Models\User;
use DateTime;
use DateTimeZone;

class QuickbooksCtl{

  public static function oauth2_begin(){
    $model = new QuickbooksAuth();
    if (!$model->begin()){
      http_response_code(400);
      echo json_encode(
        array("message" => "Unable to locate ClientID")
      );
    }
  }

  public static function oauth2_callback(){
    $model = new QuickbooksAuth();
    $model->callback();
    echo json_encode(
      array("message" => "The connection to Quickbooks is now online. You may close this window.")
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
  
}