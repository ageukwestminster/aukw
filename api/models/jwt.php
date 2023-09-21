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

/**
 * Provide properties and methods to handle creation, validation 
 * and destruction of JWT access and refresh tokens as part of the authentication process.
 * 
 * @category Model
 */
class JWTWrapper{
    
    private $config; // Jwt configuration object, contains the signer and constraints
    private $issuer;
    private $audience;
    private $cookiename;
    private $cookiepath;
    private $cookiesecure;
    private $usertoken;

    /**
     * The userid of the user
     * @var int
     */
    public $id;
    /**
     * The username of the user
     * @var string
     */
    public $user;

    public $isAdmin;
    public $role;
    public $loggedIn;
    public $expiry;
    public $jti;

    // constructor
    public function __construct(){

        $this->usertoken = new UserToken();

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

    /**
     * Get the Authorization header from the request.
     * 
     * @return string|null The header or null if no authorization header found.
     */
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

            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
    /**
     * Get the access token from the request headers.
     * 
     * @return string|null The required token or null if none found.
     */
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
     * If the request contains a cokkie by the name of $this->cookiename then return it.
     * 
     * @return string|null The required cookie or null.
     */
    private function getRefreshTokenFromCookies() {

        if(isset($_COOKIE[$this->cookiename])) {

            $cookie = $_COOKIE[$this->cookiename];

            if (!empty($cookie)) {
                return $cookie;
            }

        }

        return NULL;
    }

    /**
     * Check if the user is supplying a valid auth token and check against the database.
     * 
     * If success then update the instance properties with the JWT data and change loggin flag to true.
     * 
     * @return void Output is echo'd directly to response
     */
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
                $this->jti = $claims->get('jti');

                // Check database for existance of the JWT for the given user
                // By checking access token only this ensures refresh tokens cannot
                // be used in place of access tokens
                if ($this->usertoken->getAccessTokenStatus($this->id, $this->jti)) {
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

    /**
     * Check that there is a valid refresh token saved in the cookies.
     *  
     * @return string|null The refresh token value or null if no valid token found.
     */  
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
                    "jti" => $claims->get('jti'),
                    "expiry" => $claims->get('exp')
                );
    
                // Check database for existance of the JWT for the given user, and that it is not suspended
                if ($this->usertoken->getRefreshTokenStatus($simplified_token['id'], $simplified_token['jti'])) {
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

    public function getUserWithAccessToken(User $user){
        
        $now = new DateTimeImmutable();

        $accessTokenExpiry = $now->modify(\Core\Config::read('token.accessExpiry')); // time limit on JWT session tokens
        $accessJti = $this->GUIDv4();
        $accessToken = $this->getToken($user->id, $accessJti, $now, 
                                        $accessTokenExpiry, $user->username, $user->role);  

        $user_with_token=array(
            "username" => $user->username,
            "id" => $user->id,
            "role" => $user->role, 
            "fullname" => $user->firstname. ' ' . $user->surname,
            "accessToken" => (string)$accessToken,
            "accessJti" => $accessJti
        );
        return $user_with_token;
    }

    /**
     * Disable a refresh Token by setting the valid/invalid flag to false.
     * 
     * @return bool If update succeeds then return true, else false.
     */
    public function disableRefreshToken($refresh_token){            
        $this->usertoken->updateStatus($refresh_token['id'], $refresh_token['jti'], false);
    }

    /**
     * Disable all tokens for the given user and delete the refresh token cookie
     * 
     * Called when user logs out
     * 
     * @return bool If process succeeds then return true, else false.
     */
    public function disableAllTokens($userid){            
        $result = $this->usertoken->deleteAll($userid);
        return $result && setcookie(\Core\Config::read('token.cookiename'), '', time() - 3600);
    }

    /**
     * Create a new refresh token and put it into a cookie. Store the identifier of
     * the access and refresh tokens in the database to allow subsequent verification.
     * 
     * Called when user logs in (auth.php) or uses refresh token (refresh.php)
     * 
     * @return bool If process succeeds then return true, else false.
     */
    public function setRefreshTokenCookieFor($user_with_token, $tokenExpiry = '') {

        // Create New Token
        $now = new DateTimeImmutable();
        if (empty($tokenExpiry)) {                
            $tokenExpiry = $now->modify(\Core\Config::read('token.refreshExpiry'));
        }
        $refreshJti = $this->GUIDv4();
        $token = $this->getToken($user_with_token['id'], $refreshJti, $now, $tokenExpiry);

        setcookie($this->cookiename, $token, $tokenExpiry->getTimestamp()
            , $this->cookiepath, '', $this->cookiesecure, true); // 'true' = HttpOnly

        return $this->usertoken->store($user_with_token['id'], $user_with_token['accessJti'], $refreshJti, 
                    true, $tokenExpiry->format("Y-m-d H:i:s")); // 'true' = isValid

    }

    /**
     * Get a string representation of a new JWT using
     * 
     * @param string $userid e.g. 'admin' or 'nsc'
     * @param string $jti The identifier of the token, usually a GUID  
     * @param mixed $issuedAt The date and time the token was issued
     * @param mixed $expiresAt The date and time the token expires
     * @param string $username The username of the user
     * @param string $role The role of the user, either 'Admin' or 'User'
     * 
     * @return string A string representation of the token, in JWT format
     * 
     */
    private function getToken($userid, $jti, $issuedAt, $expiresAt, $username = '', $role = '') : string{

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

        $token = $token->identifiedBy($jti)
                       ->getToken($this->config->signer(), $this->config->signingKey());
        
        return $token->toString();
    }

    /**
     * Set the token properties to default states
     * 
     * @return void
     */
    private function initializeToken(){
        $this->user = '';
        $this->isAdmin = false;
        $this->role = '';
        $this->loggedIn = false;
        $this->expiry = '';
        $this->id = 0;
        $this->jti = '';
    }

    /**
    * Returns a GUIDv4 string. Used for token identifiers (aka jti claims).
    *
    * Uses the best cryptographically secure method
    * for all supported pltforms with fallback to an older,
    * less secure version.
    *
    * @param bool $trim If true then have no leading or trailing braces '{}'.
    * @return string The newly generated GUIDv4 string.
    */
    private function GUIDv4 ($trim = true)
    {
        $lbrace = $trim ? "" : chr(123);    // "{"
        $rbrace = $trim ? "" : chr(125);    // "}"

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
            return $lbrace.vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4)).$rbrace;
        }

        // Fallback (PHP 4.2+)
        mt_srand((int)((double)microtime() * 10000));
        $charid = strtolower(md5(uniqid(rand(), true)));
        $hyphen = chr(45);                  // "-"
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