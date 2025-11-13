<?php

/**
 * Disable all tokens for the logged-in user
 * and delete the refresh token cookie.
 *
 * Called when logging out.
 *
 */

$jwt = new \Models\JWTWrapper();

if (!$jwt->loggedIn) {
    // Keep status 200
    echo json_encode(
        array("message" => "Not logged in.")
    );
    exit(0);
} elseif ($jwt->id) {
    $jwt->disableAllTokens($jwt->id);
    echo json_encode(
        array("message" => "Logged out.")
    );
}
