<?php

namespace Models;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;
use QuickBooksOnline\API\Core\ServiceContext;

use Models\JWTWrapper;
use Models\QuickbooksToken;

use DateTime;
use DateTimeZone;

/**
 * 
 * @category Model
 */
class QuickbooksAuth{

    /**
     * An array containing the configuration of the Quickbooks API link
     * @var array
     */
    private $config;
    /**
     * QBO API object used to perform CRUD operations
     * @var DataService
     */
    private $dataService;
    /**
     * Holds QB token informaiton in persistable format
     * @var QuickbooksToken
     */
    private $tokenModel;

    /**
     * Initializes a new instance of the QuickbooksAuth class. Populates the $config property
     * with required constant values and iduser
     * @return void Output is echo'd directly to response
     */
    public function __construct(){

        $jwt = new JWTWrapper();
  
        $this->config = array(
          'auth_mode' => \Core\Config::read('qb.authmode'),
          'authorizationRequestUrl' => \Core\Config::read('qb.authrequesturi'),
          'baseUrl' => \Core\Config::read('qb.baseUrl'),
          'tokenEndPointUrl' => \Core\Config::read('qb.tokenendpointuri'),
          'ClientID' => getenv(\Core\Config::read('qb.clientid')),
          'ClientSecret' => getenv(\Core\Config::read('qb.clientsecret')),
          'scope' => \Core\Config::read('qb.authscope'),
          'redirectURI' => \Core\Config::read('qb.redirecturl'),
          'QBORealmID' => \Core\Config::read('qb.realmid'),
          'response_type' => \Core\Config::read('qb.responsetype'),
          'state' => \Core\Config::read('qb.authstate'),
          'iduser' => $jwt->id,
          'enablelog' => \Core\Config::read('qb.enablelog'),
          'loglocation' => \Core\Config::read('qb.loglocation')
        );
    }

    /**
     * Instantiate the QBO Dataservice from the config settings and 
     * Called from refresh, revoke and callback
     * @return void Output is echo'd directly to response
     */
    private function init(){
        $this->dataService = DataService::Configure($this->config);    
        $this->dataService->throwExceptionOnError(false);    

        $this->tokenModel = new QuickbooksToken();
        $this->tokenModel->read();
    }

    /** 
     * Start the OAuth2 process to create a link between QuickBooks and this app
     * @return array The Uri to follow to make the link plus instructions on what to do
     */
    public function begin() {

        $authUri=array();

        if (empty($this->config['ClientID'])) {
            return $authUri;
        }

        $this->dataService = DataService::Configure($this->config);
        $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
        $authorizationCodeUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
        return array(
            "message" => "Open this line on a new page and follow the instructions.",
            "authUri" => $authorizationCodeUrl,
            "further_information" => "https://www.scpgwiki.com/trevorsqwiki/pmwiki.php/Main/AukwQBIntegration"
        );
    }

    /** 
     * Called from Quickbooks API servers as part of the OAuth2 process 
     * @return bool 'true' if success
    */
    public function callback(){

        $this->init();
    
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
            
        try {
            // The state value is used to verify that this is a legimiate callback, not a hoax
            if ($state != $this->config['state']) {
                http_response_code(400);  
                echo json_encode(
                    array("message" => "Unable to proceed with QB callback: 'state' does not match initial value.")
                );
                exit(0);
            }

            $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
            $accessTokenObj = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);
        }
        catch (\Exception $e) {
            http_response_code(400);  
            echo json_encode(
              array("message" => "Unable to proceed with QB callback. Contact support.")
            );
            exit(0);
        }
    

        $this->dataService->updateOAuth2Token($accessTokenObj);

        $this->store_tokens_in_database($accessTokenObj);

        return true;
      }

      /**
       * Refresh the QB access token from the refresh token
       * @return true if success
       */
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
     * Break the link between this app and Quickbooks
     * @return true if success
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
     * @return true if success
     */
    public function prepare(){

        $this->tokenModel = new QuickbooksToken();
        $this->tokenModel->read();

        // Is the refresh token still valid?
        $refreshtokenexpiry = $this->tokenModel->refreshtokenexpiry;
        if ($refreshtokenexpiry == null) {
            http_response_code(400);  
            echo json_encode(
                array("message" => "QB refresh token missing from local database. Have you authorised the app?")
            );
            exit();
        }
        $refreshtokenexpiry = new DateTime($refreshtokenexpiry, new DateTimeZone('Europe/London'));
        $now = new DateTime("now", new DateTimeZone('Europe/London'));

        if($refreshtokenexpiry < $now) {
            # Uh ooh, the refresh token has expired
            http_response_code(400);  
            echo json_encode(
                array("message" => "refresh token has expired")
            );
            exit();
        }
 
        $this->dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => $this->config['ClientID'],
            'ClientSecret' => $this->config['ClientSecret'],
            'accessTokenKey' => $this->tokenModel->accesstoken,
            'refreshTokenKey' => $this->tokenModel->refreshtoken,
            'QBORealmID' => $this->config['QBORealmID'],
            'baseUrl' => $this->config['baseUrl'],
        ));

        $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
        if ($this->config['enablelog']) {
            $OAuth2LoginHelper->setLogForOAuthCalls(true, true, $this->config['loglocation']);
        }

        $accesstokenexpiry = $this->tokenModel->accesstokenexpiry;
        $accesstokenexpiry = new DateTime($accesstokenexpiry, new DateTimeZone('Europe/London'));
        if($accesstokenexpiry < $now) {
            try{
                $accessToken = $OAuth2LoginHelper->refreshToken();
            }
            catch (\Exception $e) {
                http_response_code(401);  
                echo json_encode(
                    array("message" => "Unable to get new QB Access token with supplied Refresh Token.")
                );
                exit();
            }
            $error = $OAuth2LoginHelper->getLastError();
            if ($error) {
                echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
                echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
                echo "The Response message is: " . $error->getResponseBody() . "\n";
                exit();
            }
            $this->store_tokens_in_database($accessToken);
        } else {
            $accessToken= new OAuth2AccessToken($this->config['ClientID'], $this->config['ClientSecret']);

            $accessToken->updateAccessToken(
                3600, // = The number of seconds to access token expiry
                $this->tokenModel->refreshtoken, 
                8726400, // = The number of seconds to refresh token expiry
                $this->tokenModel->accesstoken
            );
            $accessToken->setRealmID($this->config['QBORealmID']);
        }
    
        $this->dataService->updateOAuth2Token($accessToken);
    
        return $this->dataService;
    }

    /**
     * Return the ServiceContext of this DataService. 
     * Required by QBReportService.
     *
     * @return ServiceContext
     * @throws \Exception ServiceContext is NULL.
     */
    public function getServiceContext(){

        $settings = array(
            'auth_mode' => 'oauth2',
            'ClientID' => $this->config['ClientID'],
            'ClientSecret' => $this->config['ClientSecret'],
            'QBORealmID' => $this->config['QBORealmID'],
            'accessTokenKey' => $this->tokenModel->accesstoken,
            'refreshTokenKey' => $this->tokenModel->refreshtoken,
            'baseUrl' => "Production"
        );

        return ServiceContext::ConfigureFromPassedArray($settings);
    }

    /**
     * Store the access and refresh tokens in the database
     * 
     * @param OAuth2AccessToken QB object that contains access and refresh token info
     */
    private function store_tokens_in_database($accessTokenObj){

        $this->tokenModel = new QuickbooksToken();
        $this->tokenModel->read();

        if ($this->tokenModel->accesstoken) {
            $isUpdate = true;
        } else {
            $isUpdate = false;
        }

        $this->tokenModel->accesstoken = $accessTokenObj->getAccessToken();
        $this->tokenModel->refreshtoken = $accessTokenObj->getRefreshToken();

        // Expiries in the QB world are in UTC. Convert to local time
        // before saving to the database. Otherwise during BST the time
        // will be wrong by 1 hour
        $expiry = $accessTokenObj->getAccessTokenExpiresAt();
        $displayDate = new DateTime($expiry, new DateTimeZone('UTC'));
        $displayDate->setTimezone(new DateTimeZone('Europe/London'));
        $this->tokenModel->accesstokenexpiry = $displayDate->format('Y-m-d H:i:s');

        $expiry = $accessTokenObj->getRefreshTokenExpiresAt();
        $displayDate = new DateTime($expiry, new DateTimeZone('UTC'));
        $displayDate->setTimezone(new DateTimeZone('Europe/London'));
        $this->tokenModel->refreshtokenexpiry = $displayDate->format('Y-m-d H:i:s');

        if ($isUpdate) {
            return $this->tokenModel->update();
        } else {
            return $this->tokenModel->insert();
        }
    }

    /**
     * Delete the QB access and refresh tokens from the database
     * 
     * @return bool 'true' if operation succeeded
     */
    private function remove_tokens_from_database(){

        return $this->tokenModel->delete();
        
    }
}