<?php

namespace Models;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;

use Models\JWTWrapper;
use Models\QuickbooksToken;
use Models\User;

use DateTime;
use DateTimeZone;

class QuickbooksAuth{

    /**
     * An array containing the configuration of the Quickbooks API link
     * @var array
     */
    private $config;

    public $dataService;

    private $tokenModel;

    /**
     * Initializes a new instance of the QuickbooksAuth class. Populates the $config property
     *
     */
    public function __construct(){

        $jwt = new JWTWrapper();
  
        $this->config = array(
          'auth_mode' => 'oauth2',
          'authorizationRequestUrl' => 'https://appcenter.intuit.com/connect/oauth2',
          'tokenEndPointUrl' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
          'ClientID' => \Core\Config::read('qb.clientid'),
          'ClientSecret' => \Core\Config::read('qb.clientsecret'),
          'scope' => 'com.intuit.quickbooks.accounting',
          'redirectURI' => \Core\Config::read('qb.redirecturl'),
          'QBORealmID' => \Core\Config::read('qb.realmid'),
          'response_type' => 'code',
          'state' => 'TEKP56', // A random string of chars
          'iduser' => $jwt->id,
          'enablelog' => \Core\Config::read('qb.enablelog'),
          'loglocation' => \Core\Config::read('qb.loglocation')
        );
    }

    private function init(){
        $this->dataService = DataService::Configure($this->config);    
        $this->dataService->throwExceptionOnError(false);    

        $this->tokenModel = new QuickbooksToken();
        $this->tokenModel->iduser = $this->config['iduser'];
        $this->tokenModel->read();
    }

    public function begin() {

        $authUri=array();

        if (empty($this->config['ClientID'])) {
            return $authUri;
        }

        // The callback from Intuit does not contain information about which user the access token
        // applies to. We encode the iduser value in the 'state' object that is transmitted with
        // the access code.
        $this->config['state'] = $this->config['state'] . '-' . $this->config['iduser'];

        $this->dataService = DataService::Configure($this->config);
        $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
        $authorizationCodeUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
        return array(
            "message" => "Open this line on a new page and follow the instructions.",
            "authUri" => $authorizationCodeUrl,
            "further_information" => "https://www.scpgwiki.com/trevorsqwiki/pmwiki.php/Main/AukwQBIntegration"
        );
    }

    public function callback(){

        $this->init();
    
        $code = $_GET['code'];        
        $realmId = $_GET['realmId'];

        // The callback from Intuit does not contain information about which user the access token
        // applies to. We encode the iduser value in the 'state' object that is transmitted with
        // the access code.
        $stateArray = explode('-',$_GET['state']);
        $state = $stateArray[0];
        $this->config['iduser'] = $stateArray[1];

        if ($state != $this->config['state']) {
          http_response_code(400);  
          echo json_encode(
            array("message" => "Unable to proceed with QB callback: 'state' does not match initial value.")
          );
          exit(0);
        }
    
        $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
        $accessTokenObj = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);
    
        $this->dataService->updateOAuth2Token($accessTokenObj);

        $this->store_tokens_in_database($accessTokenObj);
    
        return true;
      }

    public function refresh() {

        $this->init();

        if ($this->tokenModel === NULL || $this->tokenModel->refreshtoken === NULL) {
            return false;
        }

        $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
        $accessTokenObj = $OAuth2LoginHelper->refreshAccessTokenWithRefreshToken($this->tokenModel->refreshtoken);

        $this->dataService->updateOAuth2Token($accessTokenObj);              

        $this->store_tokens_in_database($accessTokenObj);

        return true;
    }

    /**
     * Break the link between this app and Quickbooks.
     */
    public function revoke(){

        $this->init();
        if ($this->tokenModel->accesstoken) {
            $this->remove_tokens_from_database();
            $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
            return $OAuth2LoginHelper->revokeToken($this->tokenModel->accesstoken);
        } else {
            return true;
        }    
    }

    /**
     * Prepare the dataService object for API calls
     */
    public function prepare(){

        $this->tokenModel = new QuickbooksToken();
        $this->tokenModel->iduser = $this->config['iduser'];
        $this->tokenModel->read();

        // Is the refresh token still valid?
        $refreshtokenexpiry = $this->tokenModel->refreshtokenexpiry;
        if ($refreshtokenexpiry == null) {
            http_response_code(400);  
            echo json_encode(
                array("message" => "QB refresh token missing from local database. Have you authorised the app?")
            );
            return false;
        }
        $refreshtokenexpiry = new DateTime($refreshtokenexpiry, new DateTimeZone('Europe/London'));
        $now = new DateTime("now", new DateTimeZone('Europe/London'));

        if($refreshtokenexpiry < $now) {
            # Uh ooh, the refresh token has expired
            http_response_code(400);  
            echo json_encode(
                array("message" => "refresh token has expired")
            );
            return false;
        }
 
        $this->dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => $this->config['ClientID'],
            'ClientSecret' => $this->config['ClientSecret'],
            'accessTokenKey' => $this->tokenModel->accesstoken,
            'refreshTokenKey' => $this->tokenModel->refreshtoken,
            'QBORealmID' => $this->config['QBORealmID'],
            'baseUrl' => "Production"
        ));

        $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
        if ($this->config['enablelog']) {
            $OAuth2LoginHelper->setLogForOAuthCalls(true, true, $this->config['loglocation']);
        }

        $accesstokenexpiry = $this->tokenModel->accesstokenexpiry;
        $accesstokenexpiry = new DateTime($accesstokenexpiry, new DateTimeZone('Europe/London'));
        if($accesstokenexpiry < $now) {
          $accessToken = $OAuth2LoginHelper->refreshToken();
          $error = $OAuth2LoginHelper->getLastError();
          if ($error) {
              echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
              echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
              echo "The Response message is: " . $error->getResponseBody() . "\n";
              return false;
          }
          $this->store_tokens_in_database($accessToken);
        } else {
          $accessToken= new OAuth2AccessToken($this->config['ClientID'], $this->config['ClientSecret']);
          $accessToken->updateAccessToken(3600, $this->tokenModel->refreshtoken, 
                                                        8726400, $this->tokenModel->accesstoken);
          $accessToken->setRealmID($this->config['QBORealmID']);
        }
    
        $this->dataService->updateOAuth2Token($accessToken);
    
        return $this->dataService;
    }

    private function store_tokens_in_database($accessTokenObj){

        $this->tokenModel = new QuickbooksToken();
        $this->tokenModel->iduser = $this->config['iduser'];
        $this->tokenModel->read();

        $model = $this->tokenModel;

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

    private function remove_tokens_from_database(){

        $this->tokenModel->delete();
        
    }
}