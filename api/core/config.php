<?php
 /**
  * A list of api-wide constants, made available through the Config class.
  */
  
namespace Core;

// development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// production
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
//error_reporting(E_ERROR | E_WARNING | E_PARSE);

/**
 * A static class that holds application-level constants.
 * 
 * The config pattern that this classs uses is from {@link https://stackoverflow.com/a/2047999/6941165 stackoverflow}
 * 
 * @category Core
 */
class Config
{
    /**
     * Array of constants
     * @var array
     */
    static $confArray;

    /**
     * Read a constant value
     */
    public static function read($name)
    {
        return self::$confArray[$name];
    }

    /**
     * Write a constant value
     */
    public static function write($name, $value)
    {
        self::$confArray[$name] = $value;
    }

}

// server location (for Access-Origin)
Config::write('server', 'http://localhost:4200');
Config::write('api.path', '/api/');

// db
Config::write('db.host', '192.168.1.146');
Config::write('db.port', '3306');
Config::write('db.name', 'dailytakings');
Config::write('db.user', 'shop');
Config::write('db.password', 'DB_PASSWORD'); // env key name

// Note
// Environment key values (such as 'DB_PASSWORD') are stored in:
// Development: C:\Apache24\conf\httpd.conf
// Production: ~/public_html/.htaccess (!! not ./api/.htaccess)

// number of allowed password attempts
Config::write('password_attempts', 5);

// file upload settings
Config::write('file.uploaddir', './uploads/');
Config::write('file.decryptedfilename', 'payroll.xlsx');
Config::write('file.encryptedfilename', 'payroll_encrypted.xlsx');

// token settings
Config::write('token.accessExpiry', '+15 minute');
Config::write('token.refreshExpiry', '+7 day');
Config::write('token.iss', 'https://aukw.org.uk');
Config::write('token.aud', 'https://aukw.org.uk');
Config::write('token.envkeyname', 'AUKW_SHOP_KEY'); // env key name
Config::write('token.cookiename', 'refreshToken');
Config::write('token.cookiepath', Config::read('api.path') . 'auth');
Config::write('token.cookiesecure', false);

// Quickbooks SDKsettings
Config::write('qb.authmode', 'oauth2');
Config::write('qb.authrequesturi', 'https://appcenter.intuit.com/connect/oauth2');
Config::write('qb.tokenendpointuri', 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer');
Config::write('qb.authscope', 'com.intuit.quickbooks.accounting openid profile email');
Config::write('qb.responsetype', 'code');
Config::write('qb.authstate', 'TEKP567ipruY9m'); // used to verify QBO callback
Config::write('qb.enablelog', true);
Config::write('qb.loglocation', 'B:\\logs');

Config::write('qb.baseUrl', 'Production');
Config::write('qb.clientid', 'QB_CLIENT_ID'); // env key name
Config::write('qb.clientsecret', 'QB_CLIENT_SECRET'); // env key name
Config::write('qb.realmid', '9130350604308576'); // Enterprises
Config::write('qb.redirectdomain', 'https://c5b2-91-125-74-184.ngrok-free.app');
Config::write('qb.redirecturl', Config::read('qb.redirectdomain') . '/callback');

// QBO id of recurring txn that defines employee salary allocations.
Config::write('qb.allocationsid', '15654'); 

//Config::write('qb.baseUrl', 'Development');
//Config::write('qb.clientid', 'QB_SANDBOX_CLIENT_ID'); // env key name
//Config::write('qb.clientsecret', 'QB_SANDBOX_CLIENT_SECRET'); // env key name
//Config::write('qb.realmid', '4620816365027844190'); // Sandbox GB Company