<?php

/*
    'Pre' Router logic allied in this file:

    * Only logged in users can access the api, except for requests to 'auth'.
      The exception is to allow the presenting of credentials and return of tokens.

    * If it is an 'auth' request then set 'Allow-Credentials' header

    * Commands that affect data on the server (PUT/POST/DELETE etc.) require admin access

    Main routes are specified in 'routes.php'

    Router logic supplied by bramus\router (https://github.com/bramus/router)
*/

use \Core\Headers;

$router->options('/(\S+)',function() {
    Headers::getHeaders();
}); // just return headers when OPTIONS call

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
    $isUserUpdate = Headers::path_is_user($path);

    // Don't do the is-admin check when it's an 'auth' path    
    if ( !$isAuthPath ) {
        $jwt = new \Models\JWTWrapper();

        // Allow user to maintain own data
        if  (!$jwt->isAdmin && !preg_match('/user\/\d+/', $path)){

            // One exception: normal users can create and update takings data
            if (!Headers::path_is_takings_dataentry($path)) {
                http_response_code(401);  
                echo json_encode(
                    array("message" => "Must be an admin.")
                );
                exit(1);
            }
        }
    }
});