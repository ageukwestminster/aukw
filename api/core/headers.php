<?php

namespace Core;

class Headers
{

    public static function stripped_path() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $api_prefix = \Core\Config::read('api.path');
    
        if (substr($path, 0, strlen($api_prefix)) == $api_prefix) {
            $path = substr($path, strlen($api_prefix));
        } 

        return $path;
    }
    
    public static function getHeaders($path_is_auth = false) {
        if ($path_is_auth || Headers::path_is_auth()) {
            Headers::cors_headers();
        } else {
            Headers::normal_headers();
        }
    }

    // Return 'true' if the path starts with 'auth'
    public static function path_is_auth($path = '')
    {
        if (empty($path)) {
            $path = Headers::stripped_path();
        }

        return preg_match('/^auth/', $path);
    }

    // Return 'true' if the path starts with 'user'
    public static function path_is_user($path = '')
    {
        if (empty($path)) {
            $path = Headers::stripped_path();
        }

        return preg_match('/^user/', $path);
    }

    public static function path_is_user_update($path)
    {
        return false;
    }

    private static function cors_headers()
    {
        header("Access-Control-Allow-Origin: ". \Core\Config::read('server'));
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Content-Type, Access-Control-Allow-Headers, Authorization");
        header("Access-Control-Max-Age: 1728000");
        header("Content-Type: application/json; charset=UTF-8");
    }

    private static function normal_headers()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Content-Type, Access-Control-Allow-Headers, Authorization");
        header("Access-Control-Max-Age: 1728000");
        header("Content-Type: application/json; charset=UTF-8");
    }

}