<?php

namespace Core;

// development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// production
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
//error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Config pattern from https://stackoverflow.com/a/2047999/6941165
class Config
{
    static $confArray;

    public static function read($name)
    {
        return self::$confArray[$name];
    }

    public static function write($name, $value)
    {
        self::$confArray[$name] = $value;
    }

}

// server location (for Access-Origin)
Config::write('server', 'http://localhost:4200');
Config::write('api.path', '/api/');

// db
Config::write('db.host', '192.168.1.44');
Config::write('db.port', '3306');
Config::write('db.name', 'dailytakings');
Config::write('db.user', 'shop');
Config::write('db.password', 'DB_PASSWORD');

// number of allowed password attempts
Config::write('password_attempts', 5);

// token settings
Config::write('token.accessExpiry', '+15 minute');
Config::write('token.refreshExpiry', '+7 day');
Config::write('token.iss', 'https://www.aukw.org.uk');
Config::write('token.aud', 'https://www.aukw.org.uk');
Config::write('token.envkeyname', 'AUKW_SHOP_KEY');
Config::write('token.cookiename', 'refreshToken');
Config::write('token.cookiepath', Config::read('api.path') . 'auth');
Config::write('token.cookiesecure', false);

// Quickbooks SDKsettings
// PRODUCTION COMPANY
Config::write('qb.clientid', 'QB_CLIENT_ID');
Config::write('qb.clientsecret', 'QB_CLIENT_SECRET');
Config::write('qb.redirecturl', 'https://8219-86-142-147-138.ngrok.io/api/auth/callback');
Config::write('qb.realmid', '9130350604308576');
Config::write('qb.enablelog', false);
Config::write('qb.loglocation', 'B:\\logs');
