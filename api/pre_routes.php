<?php

/**
 * This file defines actions taken before the router gets to any of the actual Routes.
 * 
 * This 'Pre'-Router logic attempts to enfore these rules:
 * 1) Only logged in users can access the api, except for requests to '/auth'. 
 * Requests to '/auth' are to allow the presenting of credentials and return of tokens.
 * 
 * 2) If it is an 'auth' request then set 'Allow-Credentials' header
 * 
 * 3) Commands that affect data on the server (PUT/POST/DELETE etc.) require admin access,
 * with two exceptions: 
 *     i) Normal users can add new takings or update existing takings.
 *     ii) All users can create audit log entries 
 * 
 * Main routes are specified in the file {@link files/api-routes.html routes.php}
 * 
 * Router logic supplied by {@link https://github.com/bramus/router bramus\router}.
 */

use \Core\Headers;

/**************************************************************/
/* Just return headers when OPTIONS call                      */
/**************************************************************/
$router->options('/(\S+)',function() {
    $path = Headers::stripped_path();
    $isAuthPath = Headers::path_is_auth($path);
    Headers::getHeaders($isAuthPath);
});

/**************************************************************/
/* Before Router Middleware:                                  */
/* Auth Check: only logged-in users can access the api        */ 
/* https://github.com/bramus/router#before-router-middlewares */
/**************************************************************/
$router->before('GET|POST|PUT|DELETE|PATCH', '/.*', function() {
    
    $path = Headers::stripped_path();
    $isAuthPath = Headers::path_is_auth($path);
    $isUserPath = Headers::path_is_user($path);

    // Add Headers to the reply
    Headers::getHeaders($isAuthPath);

    // Don't do the logged-in check when it's an 'auth' path    
    if ( !$isAuthPath ) {        
        $jwt = new \Models\JWTWrapper();

        if(!$jwt->loggedIn){      
            http_response_code(401);  
            echo json_encode(
                array("message" => "Not logged in.")
            );
            exit();
        } 
        else if ($isUserPath && !$jwt->isAdmin) { // Also forbid normal users GET'ing user info
            // Except their own info, of course
            if (!preg_match('/\d+/', $path, $matches) || ($matches[0] != $jwt->id)) {
                    http_response_code(401);  
                    echo json_encode(
                        array("message" => "Must be an admin.")
                    );
                    exit(1);
            }
        }
    }
});

/**************************************************************/
/* Before Router Middleware:                                  */
/* Auth Check: only admin users can alter data                */ 
/* https://github.com/bramus/router#before-router-middlewares */
/**************************************************************/
$router->before('POST|PUT|DELETE|PATCH', '/.*', function() {

    $path = Headers::stripped_path();
    $isAuthPath = Headers::path_is_auth($path);

    // Don't do the is-admin check when it's an 'auth' path    
    if ( !$isAuthPath ) {
        $jwt = new \Models\JWTWrapper();

        // Allow user to maintain own data
        if  (!$jwt->isAdmin && !preg_match('/user\/\d+/', $path)){

            // Two exceptions: 
            //  1) normal users can create and update takings data
            //          (Normal users can't delete takings data)
            //  2) writing to audit log
            if (!Headers::path_is_takings_dataentry($path)
                && !Headers::path_is_auditlog_dataentry($path)) {
                http_response_code(401);  
                echo json_encode(
                    array("message" => "Must be an admin.")
                );
                exit(1);
            }
        }
    }
});