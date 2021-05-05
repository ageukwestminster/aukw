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
        $iduser = $jwt->id;
        $iduser = 11;

        $user = new User();
        $user->id = $iduser;
        $user->readOne();
  
        $this->config = array(
          'auth_mode' => 'oauth2',
          'authorizationRequestUrl' => 'https://appcenter.intuit.com/connect/oauth2',
          'tokenEndPointUrl' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
          'ClientID' => $user->clientid,
          'ClientSecret' => $user->clientsecret,
          'scope' => 'com.intuit.quickbooks.accounting',
          'redirectURI' => \Core\Config::read('qb.redirecturl'),
          'QBORealmID' => \Core\Config::read('qb.realmid'),
          'response_type' => 'code',
          'state' => 'TEKP56', // A random string of chars
          'iduser' => $user->id
        );
    }

    private function init(){
        $this->dataService = DataService::Configure($this->config);        

        $this->tokenModel = new QuickbooksToken();
        $this->tokenModel->iduser = $this->config['iduser'];
        $this->tokenModel->read();
    }

    public function begin() {
        $this->dataService = DataService::Configure($this->config);
        $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
        $authorizationCodeUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
        header('Location: '. $authorizationCodeUrl);
        return;
    }

    public function callback(){

        $this->init();
    
        $code = $_GET['code'];
        $state = $_GET['state'];
        $realmId = $_GET['realmId'];
    
        if ($state != $this->config['state']) {
          http_response_code(400);  
          echo json_encode(
            array("message" => "Unable to proceed with QB callback: 'state' does not match initial value.")
          );
          exit(0);
        }
    
        $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
        $accessTokenObj = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);
    
        $this->updateOAuthToken($accessTokenObj);

        $this->store_tokens_in_database($accessTokenObj);
    
        return true;
      }

    public function refresh() {

        $this->init();

        $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
        $accessTokenObj = $OAuth2LoginHelper->refreshAccessTokenWithRefreshToken($this->tokenModel->refreshtoken);

        $this->updateOAuthToken($accessTokenObj);                

        $this->store_tokens_in_database($accessTokenObj);

        return true;
    }

    /**
     * Break the link between this app and Quickbooks.
     *
     */
    public function revoke(){

        $this->init();
        $this->remove_tokens_from_database();
        $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
        return $OAuth2LoginHelper->revokeToken($this->tokenModel->accesstoken);
    
    }

    private function updateOAuthToken($accessToken){
        $this->dataService->updateOAuth2Token($accessToken);
        $error = $this->dataService->getLastError();
        if ($error) {
            http_response_code(400);  
            echo json_encode(
                array(
                    "message" => "Unable to handle callback in QB OAuth2 process.",
                    "oauth_error" => $error->getOAuthHelperError(),
                    "response_body" => $error->getResponseBody()
                    )
            );
            exit(0);
        }
    }

    private function store_tokens_in_database($accessTokenObj){

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