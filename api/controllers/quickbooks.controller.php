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

  private static function config() {

      $jwt = new JWTWrapper();
      $iduser = $jwt->id;

      $model = new User();
      $model->id = $iduser;
      $model->readOne();

      return array(
        'auth_mode' => 'oauth2',
        'authorizationRequestUrl' => 'https://appcenter.intuit.com/connect/oauth2',
        'tokenEndPointUrl' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
        'ClientID' => $model->clientid,
        'ClientSecret' => $model->clientsecret,
        'scope' => 'com.intuit.quickbooks.accounting',
        'redirectURI' => \Core\Config::read('qb.redirecturl'),
        'QBORealmID' => \Core\Config::read('qb.realmid'),
        'response_type' => 'code',
        'state' => 'TEKP56',
        'iduser' => $model->id
      );
  }


  public static function read_journal($id){  

    $model = new QuickbooksToken();
    $model->read();

    // Is the refresh token still valid?
    $refreshtokenexpiry = $model->refreshtokenexpiry;
    $refreshtokenexpiry = new DateTime($refreshtokenexpiry, new DateTimeZone('Europe/London'));
    $now = new DateTime();

    if($refreshtokenexpiry < $now) {
      # Uh ooh, the refresh token has expired
      http_response_code(400);  
      echo json_encode(
        array("message" => "refresh token has expired")
      );
      return;
    }

    // Prep Data Services
    $config = QuickbooksCtl::config();  
    $dataService = DataService::Configure(array(
        'auth_mode' => 'oauth2',
        'ClientID' => $config['ClientID'],
        'ClientSecret' => $config['ClientSecret'],
        'accessTokenKey' => $model->accesstoken,
        'refreshTokenKey' => $model->refreshtoken,
        'QBORealmID' => $config['QBORealmID'],
        'baseUrl' => "Production"
    ));


    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();  

    $accesstokenexpiry = $model->accesstokenexpiry;
    $accesstokenexpiry = new DateTime($accesstokenexpiry, new DateTimeZone('Europe/London'));
    if($accesstokenexpiry < $now) {
      $accessToken = $OAuth2LoginHelper->refreshToken();
      $error = $OAuth2LoginHelper->getLastError();
      if ($error) {
          echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
          echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
          echo "The Response message is: " . $error->getResponseBody() . "\n";
          return;
      }
      QuickbooksCtl::store_tokens($model, $accessToken);
    } else {
      $accessToken= new OAuth2AccessToken($config['ClientID'], $config['ClientSecret']);
      $accessToken->updateAccessToken(3600, $model->refreshtoken, 8726400, $model->accesstoken);
      $accessToken->setRealmID($config['QBORealmID']);
    }

    $dataService->updateOAuth2Token($accessToken);

    $dataService->setLogLocation("B:\\logs");
    $dataService->throwExceptionOnError(true);
    $dataService->forceJsonSerializers();
    $dataService->enableLog();
    $journayentry = $dataService->FindbyId('journalentry', $id);
    $error = $dataService->getLastError();
    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
    }
    else {
        //echo "Created Id={$journayentry->Id}. Reconstructed response body:\n\n";
        echo json_encode($journayentry, JSON_NUMERIC_CHECK);
        //$xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($journayentry, $urlResource);
        //echo $xmlBody . "\n";
    }
  }

  public static function add_journal(){  

    $model = new QuickbooksToken();
    $model->read();

    // Is the refresh token still valid?
    $refreshtokenexpiry = $model->refreshtokenexpiry;
    $refreshtokenexpiry = new DateTime($refreshtokenexpiry, new DateTimeZone('Europe/London'));
    $now = new DateTime();

    if($refreshtokenexpiry < $now) {
      # Uh ooh, the refresh token has expired
      http_response_code(400);  
      echo json_encode(
        array("message" => "refresh token has expired")
      );
      return;
    }

    // Prep Data Services
    $config = QuickbooksCtl::config();  
    $dataService = DataService::Configure(array(
        'auth_mode' => 'oauth2',
        'ClientID' => $config['ClientID'],
        'ClientSecret' => $config['ClientSecret'],
        'accessTokenKey' => $model->accesstoken,
        'refreshTokenKey' => $model->refreshtoken,
        'QBORealmID' => $config['QBORealmID'],
        'baseUrl' => "Production"
    ));


    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();  

    $accesstokenexpiry = $model->accesstokenexpiry;
    $accesstokenexpiry = new DateTime($accesstokenexpiry, new DateTimeZone('Europe/London'));
    if($accesstokenexpiry < $now) {
      $accessToken = $OAuth2LoginHelper->refreshToken();
      $error = $OAuth2LoginHelper->getLastError();
      if ($error) {
          echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
          echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
          echo "The Response message is: " . $error->getResponseBody() . "\n";
          return;
      }
      QuickbooksCtl::store_tokens($model, $accessToken);
    } else {
      $accessToken= new OAuth2AccessToken($config['ClientID'], $config['ClientSecret']);
      $accessToken->updateAccessToken(3600, $model->refreshtoken, 8726400, $model->accesstoken);
      $accessToken->setRealmID($config['QBORealmID']);
    }

    $dataService->updateOAuth2Token($accessToken);

    $dataService->setLogLocation("B:\\logs");
    //$dataService->throwExceptionOnError(true);
    //$dataService->forceJsonSerializers();
    $dataService->enableLog();

    $obj = [
      "TxnDate" => '2021-05-04',
      "DocNumber" => "20210504H",
      "Line" => [
      [
        "Id" => "0",
        "Description" => "Overage/Underage",
        "Amount" => 0.2,
        "DetailType" => "JournalEntryLineDetail",
        "JournalEntryLineDetail" => [
          "PostingType" => "Debit",
          "AccountRef" => [
            "value" => 93,
            "name" => "Office Expense:Cash Discrepancies"
          ],
          "ClassRef" => [
            "value" => 400000000000618070,
            "name" => "Harrow Rd"
          ]
       ]
      ],
      [
        "Id" => "1",
        "Description" => "Daily CC sales",
        "Amount" => 302.6,
        "DetailType" => "JournalEntryLineDetail",
        "JournalEntryLineDetail" => [
          "PostingType" => "Debit",
            "AccountRef" => [
              "value" => 96,
              "name" => "Credit Card Receipts"
            ],
            "ClassRef" => [
              "value" => 400000000000618070,
              "name" => "Harrow Rd"
            ]
        ]
      ],
      [
        "Id" => "2",
        "Description" => "Daily cash sales",
        "Amount" => 226,
        "DetailType" => "JournalEntryLineDetail",
        "JournalEntryLineDetail" => [
          "PostingType" => "Debit",
            "AccountRef" => [
              "value" => 100,
              "name" => "Undeposited Funds"
            ],
            "ClassRef" => [
              "value" => 400000000000618070,
              "name" => "Harrow Rd"
            ]
        ]
      ],
      [
        "Id" => "3",
        "Description" => "Zero-Rated Sales - Charity Shop Sales - Zero Rated",
        "Amount" => 528.8,
        "DetailType" => "JournalEntryLineDetail",
        "JournalEntryLineDetail" => [
          "PostingType" => "Credit",
          "Entity" => [
            "Type" => "Vendor",
            "EntityRef" => [
              "value" => 33,
              "name" => "HMRC VAT"
            ]
          ],
            "AccountRef" => [
              "value" => 94,
              "name" => "Sales-Zero Rated"
            ],
            "ClassRef" => [
              "value" => 400000000000618070,
              "name" => "Harrow Rd"
            ],
            "TaxCodeRef" => [
              "value" => 4
            ],
            "TaxApplicableOn" => "Sales",
            "TaxAmount" => 0
        ]
      ]
    ],
    "TxnTaxDetail"=> [
      "TaxLine" => [
              "Amount" => 0,
              "DetailType" => "TaxLineDetail",
              "TaxLineDetail" => [
                  "TaxRateRef" => [
                      "value" => 7
                  ],
                  "PercentBased" => true,
                  "TaxPercent" => 0,
                  "NetAmountTaxable" => -528.8
              ]
      ]
    ]
                ];
                file_put_contents('php://stderr', print_r($obj, TRUE));

    $theResourceObj = JournalEntry::create([
      "TxnDate" => '2021-05-04',
      "DocNumber" => "20210504H",
      "Line" => [
      [
        "Id" => "0",
        "Description" => "Overage/Underage",
        "Amount" => 0.2,
        "DetailType" => "JournalEntryLineDetail",
        "JournalEntryLineDetail" => [
          "PostingType" => "Debit",
          "AccountRef" => [
            "value" => 93,
            "name" => "Office Expense:Cash Discrepancies"
          ],
          "ClassRef" => [
            "value" => 400000000000618070,
            "name" => "Harrow Rd"
          ]
       ]
      ],
      [
        "Id" => "1",
        "Description" => "Zero-Rated Sales",
        "Amount" => 528.8,
        "DetailType" => "JournalEntryLineDetail",
        "JournalEntryLineDetail" => [
          "PostingType" => "Credit",
          "Entity" => [
            "Type" => "Vendor",
            "EntityRef" => [
              "value" => 33,
              "name" => "HMRC VAT"
            ]
          ],
            "AccountRef" => [
              "value" => 94,
              "name" => "Sales-Zero Rated"
            ],
            "ClassRef" => [
              "value" => 400000000000618070,
              "name" => "Harrow Rd"
            ],
            "TaxCodeRef" => [
              "value" => 4
            ],
            "TaxApplicableOn" => "Sales",
            "TaxAmount" => 0
        ]
      ]
    ],
    "TxnTaxDetail"=> [
      "TaxLine" => [
              "Amount" => 0,
              "DetailType" => "TaxLineDetail",
              "TaxLineDetail" => [
                  "TaxRateRef" => [
                      "value" => 7
                  ],
                  "PercentBased" => true,
                  "TaxPercent" => 0,
                  "NetAmountTaxable" => -528.8
              ]
      ]
    ]
  ]);
  
  file_put_contents('php://stderr', print_r($theResourceObj, TRUE));

  /*$theResourceObj = JournalEntry::create([
    "Line" => [
    [
      "Id" => "0",
      "Description" => "Test Deposit",
      "Amount" => 100.0,
      "DetailType" => "JournalEntryLineDetail",
      "JournalEntryLineDetail" => [
        "PostingType" => "Debit",
        "AccountRef" => [
            "value" => 100,
            "name" => "Undeposited Funds"
        ]
     ]
    ],
    [
      "Description" => "nov portion of rider insurance",
      "Amount" => 100.0,
      "DetailType" => "JournalEntryLineDetail",
      "JournalEntryLineDetail" => [
        "PostingType" => "Credit",
          "AccountRef" => [
            "value" => 100,
            "name" => "Undeposited Funds"
          ]
      ]
    ]
  ]
]);

file_put_contents('php://stderr', print_r($theResourceObj, TRUE));*/


  $resultingObj = $dataService->Add($theResourceObj);
    $error = $dataService->getLastError();
    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
    }
    else {
        //echo "Created Id={$journayentry->Id}. Reconstructed response body:\n\n";
        echo json_encode($resultingObj , JSON_NUMERIC_CHECK);
        //$xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($journayentry, $urlResource);
        //echo $xmlBody . "\n";
    }
  }

/*
  public static function oauth2_begin(){
    $dataService = DataService::Configure(QuickbooksCtl::config());
    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
    $authorizationCodeUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
    header('Location: '. $authorizationCodeUrl);
    return;
  }
  
    public static function oauth2_callback(){

    $config = QuickbooksCtl::config();
    $dataService = DataService::Configure($config);
    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

    $code = $_GET['code'];
    $state = $_GET['state'];
    $realmId = $_GET['realmId'];

    if ($state != $config['state']) {
      http_response_code(400);  
      echo json_encode(
        array("message" => "Unable to proceed with QB callback: 'state' does not match initial value.")
      );
      return;
    }

    $accessTokenObj = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);

    $dataService->updateOAuth2Token($accessTokenObj);

    $model = new QuickbooksToken();
    $model->iduser=11;
    $model->read();
    QuickbooksCtl::store_tokens($model, $accessTokenObj);

    echo json_encode(
      array("message" => "QB Tokens stored")
    );
  }

  public static function oauth2_refresh(){

    $dataService = DataService::Configure(QuickbooksCtl::config());
    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

    $model = new QuickbooksToken();
  
    $model->read();
    $accessTokenObj = $OAuth2LoginHelper->
                refreshAccessTokenWithRefreshToken($model->refreshtoken);

    $dataService->updateOAuth2Token($accessTokenObj);                
    QuickbooksCtl::store_tokens($model, $accessTokenObj);

    echo json_encode(
      array("message" => "QB Tokens refreshed")
    );
  }

  public static function oauth2_revoke(){

    $dataService = DataService::Configure(QuickbooksCtl::config());
    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

    $model = new QuickbooksToken();
    $model->read();
    
    $result = $OAuth2LoginHelper->revokeToken($model->accesstoken);

    if ($result) {
      $model->delete();
      echo json_encode(
        array(
          "message" => "All QB tokens revoked."
        )
      , JSON_NUMERIC_CHECK);
    } else {
      http_response_code(400);  
      echo json_encode(
        array("message" => "Unable to revoke QB tokens.")
      );
    }
    
    
  }

  private static function store_tokens($model, $accessTokenObj){

    if ($model->accesstoken) {
      $isUpdate = true;
    } else {
      $isUpdate = false;
    }

    $model->accesstoken = $accessTokenObj->getAccessToken();
    $model->refreshtoken = $accessTokenObj->getRefreshToken();

    // Expiries in the QB world are in UTC. Convert to local time
    // before saving to the database. Otherwise during BST the time
    // will be wrong by 1 hour
    $expiry = $accessTokenObj->getAccessTokenExpiresAt();
    $displayDate = new DateTime($expiry, new DateTimeZone('UTC'));
    $displayDate->setTimezone(new DateTimeZone('Europe/London'));
    $model->accesstokenexpiry = $displayDate->format('Y-m-d H:i:s');

    $expiry = $accessTokenObj->getRefreshTokenExpiresAt();
    $displayDate = new DateTime($expiry, new DateTimeZone('UTC'));
    $displayDate->setTimezone(new DateTimeZone('Europe/London'));
    $model->refreshtokenexpiry = $displayDate->format('Y-m-d H:i:s');

    if ($isUpdate) {
      return $model->update();
    } else {
      return $model->insert();
    }
  }
  */

  public static function oauth2_begin(){
    $model = new QuickbooksAuth();
    $model->begin();
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