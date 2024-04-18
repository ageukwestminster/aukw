<?php

namespace Models;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;
use QuickBooksOnline\API\Core\ServiceContext;

use Models\JWTWrapper;
use Models\QuickbooksToken;

use DateTime;
use DateTimeZone;

/**
 * Wrapper class that provides methods for QBO OAuth2.
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
    private DataService $dataService;
    /**
     * Holds QB token informaiton in persistable format
     * @var QuickbooksToken
     */
    private QuickbooksToken $tokenModel;
    /**
     * The access token of the currently logged in user
     * @var JWTWrapper
     */
    private JWTWrapper $jwt;

    /**
     * Initializes a new instance of the QuickbooksAuth class. Populates the $config property
     * with required constant values and iduser
     * @return void Output is echo'd directly to response
     */
    public function __construct(){

        $this->jwt = new JWTWrapper();
  
        $this->config = array(
          'auth_mode' => \Core\Config::read('qb.authmode'),
          'authorizationRequestUrl' => \Core\Config::read('qb.authrequesturi'),
          'baseUrl' => \Core\Config::read('qb.baseUrl'),
          'tokenEndPointUrl' => \Core\Config::read('qb.tokenendpointuri'),
          'ClientID' => getenv(\Core\Config::read('qb.clientid')),
          'ClientSecret' => getenv(\Core\Config::read('qb.clientsecret')),
          'scope' => \Core\Config::read('qb.authscope'),
          'redirectURI' => \Core\Config::read('qb.redirecturl'),
          'response_type' => \Core\Config::read('qb.responsetype'),
          'state' => \Core\Config::read('qb.authstate'),
          'iduser' => $this->jwt->id,
          'enablelog' => \Core\Config::read('qb.enablelog'),
          'loglocation' => \Core\Config::read('qb.loglocation')
        );
    }

    /**
     * Instantiate the QBO Dataservice from the config settings 
     * Called from refresh, revoke and callback
     * @return void Output is echo'd directly to response
     */
    private function init($realmId = ''){

        if (!empty($realmId)) {
            $this->config['QBORealmID'] = $realmId;
        }

        $this->dataService = DataService::Configure($this->config);    
        $this->dataService->throwExceptionOnError(false);    

        $this->tokenModel = new QuickbooksToken();        
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
     * @param string $code
     * @param string $realmId
     * @param string $state
     * @return bool 'true' if success
    */
    public function callback($code, $realmId, $state){

        $this->init($realmId);
            
        try {
            // The state value is used to verify that this is a legitimiate callback, not a hoax
            if ($state != $this->config['state']) {
                http_response_code(400);  
                echo json_encode(
                    array("message" => "Unable to proceed with QB callback: 'state' does not match initial value.")
                );
                exit(0);
            }

            //$accessTokenObj = QuickbooksDummyAuthProvider::getToken($this->config);
            //$userInfo = QuickbooksDummyAuthProvider::getUserInfo();
            
            $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
            $accessTokenObj = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);

            $this->dataService->updateOAuth2Token($accessTokenObj);

            $userInfo = $OAuth2LoginHelper->getUserInfo($accessTokenObj->getAccessToken(), 
                                        strtolower($this->config['baseUrl']));

            if (!$userInfo['emailVerified']) {
                http_response_code(400);  
                echo json_encode(
                  array("message" => "Your Quickbooks email address is not verified. Please " . 
                    "verify it at https://accounts.intuit.com/app/account-manager/security and try again.")
                );
                exit(0);
            } 
                                
            
            $user = new \Models\User();
            $user->firstname = $userInfo['givenName'];
            $user->surname = $userInfo['familyName'];
            $user->email = $userInfo['email'];
            
            $user->readOneByNameAndEmail();
        
            // Is user in database? if not then create
            if (empty($user->username) ) {                
                $user->username = $userInfo['email'];
                $user->create();
            } 
            
            // Has user got QBO id link? If not then update user
            if ($user->quickbooksUserId != $userInfo['sub']) {
                // update to add QB sub
                $user->quickbooksUserId = $userInfo['sub'];
                $user->update();
            }

            // Store QB tokens
            $this->store_tokens_in_database($user->id, $accessTokenObj);

            // Generate user tokens
            $jwt = new \Models\JWTWrapper();
            $user_with_token = $jwt->getUserWithAccessToken($user);

            //Create a new refresh token and put it into a cookie.
            // Also store in databse
            $jwt->setRefreshTokenCookieFor($user_with_token);

            // Return logged in info
            echo json_encode($user_with_token);            
            
        }
        catch (\Exception $e) {
            http_response_code(400);  
            echo json_encode(
              array("message" => "Unable to proceed with QB callback.",
              "details" => $e->getMessage())
            );
            exit(0);
        }
    
        return true;
      }

      /**
       * Refresh the QB access token from the refresh token
       * @param string $realmid The company ID f
       * 
       * @return true if success
       */
    public function refresh($userid, $realmid) {

        if($this->jwt->id != $userid) {
            http_response_code(401);  
            echo json_encode(
              array("message" => "Unable to refresh Quickbooks tokens, userid mismatch.",
              "details" => "JWT userid = '".$this->jwt->id."', provided userid = '".$userid."'.")
            );
            exit(0);
        }

        $this->init($realmid);

        $this->tokenModel->read($userid, $realmid);

        if ($this->tokenModel === NULL || $this->tokenModel->refreshtoken === NULL) {
            return false;
        }

        try {
            $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
            $accessTokenObj = $OAuth2LoginHelper->refreshAccessTokenWithRefreshToken(
                            $this->tokenModel->refreshtoken);
                            
            if (!$accessTokenObj->getRealmID()) {
                $accessTokenObj->setRealmID($realmid);
            }

            $this->dataService->updateOAuth2Token($accessTokenObj);      

            $this->store_tokens_in_database($userid, $accessTokenObj);
        }
        catch (\Exception $e) {
            http_response_code(400);  
            echo json_encode(
              array("message" => "Unable to refresh Quickbooks tokens. ",
              "details" => $e->getMessage())
            );
            exit(0);
        }

        return true;
    }

     /**
       * Get information abou the company
       * @param string $realmid The company ID for the QBO company.
       * @return array info aboout the company
       */
      public function companyInfo($realmid) {

        $this->init($realmid);

        $this->tokenModel->read($this->jwt->id, $realmid);
            
        if ($this->tokenModel === NULL || $this->tokenModel->refreshtoken === NULL) {
            return false;
        }

        try {
            $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
            $accessTokenObj = $OAuth2LoginHelper->refreshAccessTokenWithRefreshToken(
                $this->tokenModel->refreshtoken);
                            
            if (!$accessTokenObj->getRealmID()) {
                $accessTokenObj->setRealmID($realmid);
            }

            $this->dataService->updateOAuth2Token($accessTokenObj);      

            $this->store_tokens_in_database($this->jwt->id, $accessTokenObj);

            return $this->dataService->getCompanyInfo();
        }
        catch (\Exception $e) {
            http_response_code(400);  
            echo json_encode(
              array("message" => "Unable to refresh Quickbooks tokens. ",
              "details" => $e->getMessage())
            );
            exit(0);
        }

        return true;
    }

    /**
     * Break the link between this app and Quickbooks
     * @param int $userid The user id of the User
     * @param string $realmid The company ID for the QBO company.
     * @return true if success
     */
    public function revoke($userid, $realmid) {

        $this->init($realmid);

        $this->tokenModel->read($userid, $realmid);

        if ($this->tokenModel->accesstoken) {
            $this->remove_tokens_from_database();
            $OAuth2LoginHelper = $this->dataService->getOAuth2LoginHelper();
            return $OAuth2LoginHelper->revokeToken($this->tokenModel->accesstoken);
        } else {
            return true;
        }    
    }

    /**
     * Prepare the dataService object for API calls. Called before all QBO api calls.
     * 
     * Process:
     *      * Check for a valid refresh token. If none or expired then suggest re-authorising the app
     *      * Check if access token expired. If yes then use refresh token to obtain new access token.
     *      * Configure a new DataService object from the given realID and default values.
     *      * Append the new or unexpired access token to the QBO DataService object
     *      * Return this prepared DataService object
     * 
     * @return DataService|null 
     */
    public function prepare($realmid){

        $this->tokenModel = new QuickbooksToken();
        $this->tokenModel->read($this->jwt->id, $realmid);

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
                array("message" => "Refresh token has expired. Please re-authorise the app.")
            );
            exit();
        }
 
        $this->dataService = DataService::Configure(array(
            'auth_mode' => $this->config['auth_mode'],
            'ClientID' => $this->config['ClientID'],
            'ClientSecret' => $this->config['ClientSecret'],
            'accessTokenKey' => $this->tokenModel->accesstoken,
            'refreshTokenKey' => $this->tokenModel->refreshtoken,
            'QBORealmID' => $realmid,
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
            $this->store_tokens_in_database($this->jwt->id, $accessToken);
        } else {
            $accessToken= new OAuth2AccessToken($this->config['ClientID'], $this->config['ClientSecret']);

            $accessToken->updateAccessToken(
                3600, // = The number of seconds to access token expiry
                $this->tokenModel->refreshtoken, 
                8726400, // = The number of seconds to refresh token expiry
                $this->tokenModel->accesstoken
            );
            $accessToken->setRealmID($realmid);
        }
    
        $this->dataService->updateOAuth2Token($accessToken);
    
        return $this->dataService;
    }

    /**
     * Return the ServiceContext of this DataService. 
     * Required by QBReportService.
     * @param string $realmid QBO Company id
     * @return ServiceContext
     * @throws \Exception ServiceContext is NULL.
     */
    public function getServiceContext($realmid){

        $settings = array(
            'auth_mode' => 'oauth2',
            'ClientID' => $this->config['ClientID'],
            'ClientSecret' => $this->config['ClientSecret'],
            'QBORealmID' => $realmid,
            'accessTokenKey' => $this->tokenModel->accesstoken,
            'refreshTokenKey' => $this->tokenModel->refreshtoken,
            'baseUrl' => "Production"
        );

        return ServiceContext::ConfigureFromPassedArray($settings);
    }

    /**
     * Store the access and refresh tokens in the database.
     * Called by callback(), refresh() and prepare().
     * @param OAuth2AccessToken QB object that contains access and refresh token info
     * @return bool 'true' if operation succeeded
     */
    private function store_tokens_in_database(int $userid, OAuth2AccessToken $accessTokenObj){

        $this->tokenModel = new QuickbooksToken();
        $realmid = $accessTokenObj->getRealmID();
        $this->tokenModel->read($userid, $realmid);

        if ($this->tokenModel->accesstoken) {
            $isUpdate = true;
        } else {
            $isUpdate = false;
            $this->tokenModel->userid = $userid;
            $this->tokenModel->realmid = $realmid;
        }

        $this->tokenModel->accesstoken = $accessTokenObj->getAccessToken();
        $this->tokenModel->refreshtoken = $accessTokenObj->getRefreshToken();

        // Expiries in the QB world are in UTC. Convert to local time
        // before saving to the database. Otherwise during BST the time
        // will be wrong by 1 hour
        $expiry = $accessTokenObj->getAccessTokenExpiresAt();

        /** @disregard Ignore Intelephense error on next line */
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