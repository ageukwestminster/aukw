<?php

namespace Core;

/**
 * A helper class that provides methoids to return HTTP headers.
 * @category Core
 */
class Headers
{
    /**
     * Return the part of the path that is after .../api/
     * Used to simplify the path before checking if (for example) it is a 'auth' path.
     * 
     * @return string
     */
    public static function stripped_path() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $api_prefix = \Core\Config::read('api.path');
    
        if (substr($path, 0, strlen($api_prefix)) == $api_prefix) {
            $path = substr($path, strlen($api_prefix));
        } 

        return $path;
    }
    
    /**
     * Respond with the raw CORS or non-CORS headers as appropiate.
     * CORS headers are used when the request is part of the authentication process
     *      
     * @return void
     */
    public static function getHeaders($path_is_auth = false) {
        if ($path_is_auth) {
            Headers::cors_headers();
        } else {
            Headers::normal_headers();
        }
    }

    /**
     * Return 'true' if the path is an authentication-related path. These
     * paths do not require the user to be logged before accessing them.
     * 
     * @return bool 'true' if the path is for authentication purposes
     */
    public static function path_is_auth($path = '')
    {
        if (empty($path)) {
            $path = Headers::stripped_path();
        }

        // QBO callback when making connection
        if (preg_match('/^qb\/callback/', $path)) {
            return true;
        }

        // Normal login/logout auth path
        return preg_match('/^auth/', $path);
    }

    /**
     * Return 'true' if the path is either of these two routes:
     * POST 'takings/' or PUT  'takings/(\d+)'
     * 
     * @return string
     */
    public static function path_is_takings_dataentry($path = '')
    {
        if (empty($path)) {
            $path = Headers::stripped_path();
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $value = preg_match('/^takings\/(\d+)/', $path);

        return ($method === 'POST' && preg_match('/^takings/', $path)) ||
            ($method === 'PUT' && preg_match('/^takings\/(\d+)/', $path));
    }

    /** 
     * Return 'true' if the path starts with 'user'. Used to determine if
     * the route requested requires special authorization.
     * 
     * @return bool
     * */
    public static function path_is_user($path = '')
    {
        if (empty($path)) {
            $path = Headers::stripped_path();
        }

        return preg_match('/^user/', $path);
    }

    /**
     * Respond with the raw CORS headers for authentication requests
     *      
     * @return void
     */
    private static function cors_headers()
    {
        header("Access-Control-Allow-Origin: ". \Core\Config::read('server'));
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Content-Type, Access-Control-Allow-Headers, Authorization");
        header("Access-Control-Max-Age: 1728000");
        header("Content-Type: application/json; charset=UTF-8");
    }

    /**
     * Respond with the raw non-CORS headers for simple requests
     *      
     * @return void
     */
    private static function normal_headers()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Content-Type, Access-Control-Allow-Headers, Authorization");
        header("Access-Control-Max-Age: 1728000");
        header("Content-Type: application/json; charset=UTF-8");
    }

}