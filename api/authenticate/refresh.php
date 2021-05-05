<?php

// instantiate JWT and user object
$jwt = new \Models\JWTWrapper();
$user = new \Models\User();
$token = $jwt->validateRefreshToken();

if ($token && $token['id']) {

    $jwt->disableOldToken($token);

    // read the details of user to be edited
    $user->id = $token['id'];
    $user->readOne();
    if (empty($user->username) ) {
        http_response_code(400);   
        echo json_encode(
            array("message" => "No User found with id = " . $user->id)
        );
        exit(1);
    }
    

    $user_with_token = $jwt->getAccessToken($user);    

    $jwt->setRefreshTokenCookieFor($user_with_token, $token['expiry']);

    echo json_encode($user_with_token);
}
else {
    http_response_code(401); 
    echo json_encode(
        array("message" => "Unauthorized")
    );
}