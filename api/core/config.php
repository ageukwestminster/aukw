<?php

namespace Core;

// development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// production
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
//error_reporting(E_ERROR | E_WARNING | E_PARSE);

/**
 * The Config class in config.php is designed to hold application-level constants and configuration 
 * settings. It uses a static array to store these values and provides methods to read and write them. 
 * The file also includes some initial configuration settings for development and production environments.
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
     *      
     * @param string $name The name of the constant
     * @return mixed The value of the constant
     */
    public static function read(string $name)
    {
        return self::$confArray[$name];
    }

    /**
     * Write a constant value
     * 
     * @param string $name The name of the constant
     * @param mixed $value The value of the constant
     * @return void
     */
    public static function write(string $name, $value): void
    {
        self::$confArray[$name] = $value;
    }

}

// server location (for Access-Origin)
Config::write('server', 'http://localhost:4200'); // Must change when deploying to production
Config::write('api.path', '/api/');

// db
Config::write('db.host', 'themis');             // Database IP or hostname. Usually 'localhost' on produciton
Config::write('db.port', '3306');               // standard MySql / MariaDB port
Config::write('db.name', 'aukworgu_dailytakings'); // Database name
Config::write('db.user', 'aukworgu_shop');      // Database user. All database actions are performed by this single user.
Config::write('db.password', 'DB_PASSWORD');    // Database user's password is stored as enviornment variable

// Note
// Environment key values (such as 'DB_PASSWORD') are stored in:
// Development: C:\Apache24\conf\httpd.conf
// Production: ~/public_html/.htaccess (!! not ./api/.htaccess)

// number of allowed password attempts. user is suspended if fails to login 6 times in a row
Config::write('password_attempts', 5);

// token settings
Config::write('token.accessExpiry', '+15 minute');
Config::write('token.refreshExpiry', '+7 day');
Config::write('token.iss', 'https://aukw.org.uk');
Config::write('token.aud', 'https://aukw.org.uk');
Config::write('token.envkeyname', 'AUKW_SHOP_KEY'); // environment variable name
Config::write('token.cookiename', 'refreshToken');
Config::write('token.cookiepath', Config::read('api.path') . 'auth');
Config::write('token.cookiesecure', false);

// QuickBooks SDK settings
Config::write('qb.authmode', 'oauth2');
Config::write('qb.authrequesturi', 'https://appcenter.intuit.com/connect/oauth2');
Config::write('qb.tokenendpointuri', 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer');
Config::write('qb.authscope', 'com.intuit.quickbooks.accounting openid profile email');
Config::write('qb.responsetype', 'code');
Config::write('qb.authstate', 'QB_AUTH_STATE'); // used to verify QBO callback, environment variable name
Config::write('qb.enablelog', true);
Config::write('qb.loglocation', 'B:\\logs'); 
// Note: most logging goes into developer's %temp% directory
// Only OAuth request and response end up in qb.loglocation

Config::write('qb.baseUrl', 'Production');
Config::write('qb.clientid', 'QB_CLIENT_ID'); // environment variable name
Config::write('qb.clientsecret', 'QB_CLIENT_SECRET'); // environment variable name
Config::write('qb.redirectdomain', 'https://e6c7-91-125-74-184.ngrok-free.app');
Config::write('qb.redirecturl', Config::read('qb.redirectdomain') . '/callback');

// QBO id of recurring txn that defines employee salary allocations.
Config::write('qb.allocationsid', '15654'); 

// Staffology API settings
Config::write('staffology.apiurl', 'https://api.staffology.co.uk/');
Config::write('staffology.apikey', 'STAFFOLOGY_API_KEY'); // environment variable name
Config::write('staffology.payperiod', 'Monthly');