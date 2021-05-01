<?php

// This code uses Luis Cobucci' implementation of JWT: https://github.com/lcobucci/jwt/

namespace Models;

use DateTimeImmutable;

// /https://lcobucci-jwt.readthedocs.io/en/latest/upgrading/
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;

class JWTWrapper{
    
    private $config; // Jwt configuration object, contains the signer and constraints
    private $issuer;
    private $audience;
    private $cookiename;
    private $cookiepath;
    private $cookiesecure;
    private $usertoken;

    // object properties
    public $id;
    public $user;
    public $isAdmin;
    public $role;
    public $loggedIn;
    public $expiry;
    public $hash;

    // constructor
    public function __construct(){

        $this->usertoken = new UserToken(\Core\Database::getInstance()->conn);

        $this->config = Configuration::forSymmetricSigner(
            // You may use any HMAC variations (256, 384, and 512)
            new Sha256(),
            // replace the value below with a key of your own!
            InMemory::plainText( getenv(\Core\Config::read('token.envkeyname')) )
            // You may also override the JOSE encoder/decoder if needed by providing extra arguments here
        );

        $clock = new FrozenClock(new DateTimeImmutable());

        $this->issuer = \Core\Config::read('token.iss');
        $this->audience = \Core\Config::read('token.aud');
        $this->cookiename = \Core\Config::read('token.cookiename');
        $this->cookiepath = \Core\Config::read('token.cookiepath');
        $this->cookiesecure = \Core\Config::read('token.cookiesecure');

        $this->config->setValidationConstraints(
            new SignedWith($this->config->signer(), $this->config->verificationKey()),
            new PermittedFor($this->audience),
            new IssuedBy($this->issuer),
            new LooseValidAt($clock)
        );

        $this->initializeToken();
        
        $this->checkAuth();
    }

    private function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
    /**
    * get access token from header
    * */
    private function getAccessTokenFromHeaders() {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return NULL;
    }

    /**
    * get refresh token
    * */
    private function getRefreshTokenFromCookies() {

        if(isset($_COOKIE[$this->cookiename])) {

            $cookie = $_COOKIE[$this->cookiename];

            if (!empty($cookie)) {
                return $cookie;
            }

        }

        return NULL;
    }

    // Check the headers for the presence of a valid JWT
    public function checkAuth(){

        $token = $this->getAccessTokenFromHeaders(); // get token from Auth header
        
        // If no token just exit
        if( is_null($token) ){
            $this->initializeToken();
            return;
        }

        // convert from string into JWT object
        $parser = $this->config->parser();
        $token = $parser->parse((string) $token);

        $token->headers(); // Retrieves the token headers
        $claims = $token->claims(); // Retrieves the token claims

        $constraints = $this->config->validationConstraints();      
        
        // If its a valid token then update the class properties
        // the '...' means to pass an array as function arguments
        try {
            if($this->config->validator()->validate($token, ...$constraints)){
                $this->id = $claims->get('sub');
                $this->user = $claims->get('user');
                $this->isAdmin=$claims->get('role')=='Admin'?true:false;
                $this->role=$claims->get('role');
                $this->expiry = $claims->get('exp')->format("Y-m-d H:i:s");
                $this->hash = $claims->get('jti');

                // Check database for existance of the JWT for the given user
                // By checking access token only this ensures refresh tokens cannot
                // be used in place of access tokens
                if ($this->usertoken->getAccessTokenStatus($this->id, $this->hash)) {
                    $this->loggedIn = true;
                } else {
                    $this->initializeToken();
                }
            }
            else {
                $this->initializeToken();
            }
        }
        catch (\Exception $e) {
            $this->initializeToken();
        }
    }

        
        public function validateRefreshToken(){
            $token = $this->getRefreshTokenFromCookies(); // get token from Auth header

            // If no token just exit
            if( is_null($token) ){
                return NULL;
            }
    
            // convert from string into JWT object
            $parser = $this->config->parser();
            $token = $parser->parse((string) $token);
    
            $token->headers(); // Retrieves the token headers
            $claims = $token->claims(); // Retrieves the token claims
    
            $constraints = $this->config->validationConstraints();      
            
            // If its a valid token then update the class properties
            // the '...' means to pass an array as function arguments
            try {
                if($this->config->validator()->validate($token, ...$constraints)){
                    $simplified_token=array(
                        "id" => $claims->get('sub'),
                        "hash" => $claims->get('jti'),
                        "expiry" => $claims->get('exp')
                    );
        
                    // Check database for existance of the JWT for the given user, and that it is not suspeded
                    if ($this->usertoken->getRefreshTokenStatus($simplified_token['id'], $simplified_token['hash'])) {
                        return $simplified_token;
                    } else {
                        // Someone has used a valid RefreshToken that has already been used (status = 0)
                        // Disable all tokens and force user to log in again

                        // TODO: This is being called too often
                        //$this->disableAllTokens($simplified_token['id']);                        
                        return NULL;
                    }
                }
                else {
                    return NULL;
                }
            }
            catch (\Exception $e) {
                return NULL;
            }
        }

        public function getAccessToken(User $user){
            
            $now = new DateTimeImmutable();

            $accessTokenExpiry = $now->modify(\Core\Config::read('token.accessExpiry')); // time limit on JWT session tokens
            $accessHash = $this->GUIDv4();
            $accessToken = $this->getToken($user->id, $accessHash, $now, 
                                            $accessTokenExpiry, $user->username, $user->role);  

            $user_with_token=array(
                "username" => $user->username,
                "id" => $user->id,
                "role" => $user->role, 
                "fullname" => $user->firstname. ' ' . $user->surname,
                "accessToken" => (string)$accessToken,
                "accessHash" => $accessHash
            );
            return $user_with_token;
        }

        // Disable any tokens created from the old Refresh Token and the Refresh Token itself
        public function disableOldToken($token){            
            $this->usertoken->updateStatus($token['id'], $token['hash'], false);
        }

        // Disable any tokens created from the old Refresh Token and the Refresh Token itself
        public function disableAllTokens($userid){            
            $this->usertoken->deleteAll($userid);
            setcookie(\Core\Config::read('token.cookiename'), '', time() - 3600);
        }

        public function setRefreshTokenCookieFor($user_with_token, $tokenExpiry = '') {

            // Create New Token
            $now = new DateTimeImmutable();
            if (empty($tokenExpiry)) {                
                $tokenExpiry = $now->modify(\Core\Config::read('token.refreshExpiry'));
            }
            $hash = $this->GUIDv4();
            $token = $this->getToken($user_with_token['id'], $hash, $now, $tokenExpiry);

            setcookie($this->cookiename, $token, $tokenExpiry->getTimestamp()
                , $this->cookiepath, '', $this->cookiesecure, true); // 'true' = HttpOnly

            $this->usertoken->store($user_with_token['id'], $user_with_token['accessHash'], $hash, 
                        true, $tokenExpiry->format("Y-m-d H:i:s")); // 'true' = isValid

        }


    // Get a string representation of a new JWT
    private function getToken($userid, $hash, $issuedAt, $expiresAt, $username = '', $role = ''){

        $builder = $this->config->builder();

        $token = $builder->issuedBy($this->issuer)
                                ->withHeader('iss', $this->issuer)
                                ->permittedFor($this->audience)
                                ->issuedAt($issuedAt)
                                ->expiresAt($expiresAt)
                                ->relatedTo($userid);

        if (!empty($username)) {
        $token = $token->withClaim('user', $username);
        }

        if (!empty($role)) {
        $token = $token->withClaim('role', $role);
        }

        $token = $token->identifiedBy($hash)
                                ->getToken($this->config->signer(), $this->config->signingKey());
        
        return $token->toString();
    }

    /*
        Set the token properties to default states
    */
    private function initializeToken(){
        $this->user = '';
        $this->isAdmin = false;
        $this->role = '';
        $this->loggedIn = false;
        $this->expiry = '';
        $this->id = 0;
        $this->hash = '';
    }

    /**
    * Returns a GUIDv4 string
    *
    * Uses the best cryptographically secure method
    * for all supported pltforms with fallback to an older,
    * less secure version.
    *
    * @param bool $trim
    * @return string
    */
    private function GUIDv4 ($trim = true)
    {
        // Windows
        if (function_exists('com_create_guid') === true) {
            if ($trim === true)
                return trim(com_create_guid(), '{}');
            else
                return com_create_guid();
        }

        // OSX/Linux
        if (function_exists('openssl_random_pseudo_bytes') === true) {
            $data = openssl_random_pseudo_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }

        // Fallback (PHP 4.2+)
        mt_srand((double)microtime() * 10000);
        $charid = strtolower(md5(uniqid(rand(), true)));
        $hyphen = chr(45);                  // "-"
        $lbrace = $trim ? "" : chr(123);    // "{"
        $rbrace = $trim ? "" : chr(125);    // "}"
        $guidv4 = $lbrace.
                substr($charid,  0,  8).$hyphen.
                substr($charid,  8,  4).$hyphen.
                substr($charid, 12,  4).$hyphen.
                substr($charid, 16,  4).$hyphen.
                substr($charid, 20, 12).
                $rbrace;
        return $guidv4;
    }
}