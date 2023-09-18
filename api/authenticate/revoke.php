<?php

$jwt = new \Models\JWTWrapper();

/**
 * If logged in then disable all tokens for the logged-in user
 * and delete the refresh token cookie.
 * 
 */
if(!$jwt->loggedIn){    
    // Keep status 200
    echo json_encode(
        array("message" => "Not logged in.")
    );  
    exit(0);
} else if ($jwt->id) {
    $jwt->disableAllTokens($jwt->id);
    echo json_encode(
        array("message" => "Logged out.")
    ); 
}