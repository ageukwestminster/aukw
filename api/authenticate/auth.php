 <?php
 /**
 * Check if the user credentials provided via POST match
 * any of the credentials saved in the database.
 * 
 * If success then respond with user and token data
 * If failure reply with 410 http code and error message 
 * 
 * Called when logging in.
 */

header("Access-Control-Allow-Credentials: true");

// instantiate database and user object
$db = \Core\Database::getInstance()->conn;
$user = new \Models\User($db);
$usertoken = new \Models\UserToken($db);
$usernm = '';
$pass = '';
$numberPasswordAttempts = \Core\Config::read('password_attempts');

// get posted data
$data = json_decode(file_get_contents("php://input"));
// set property values
if (isset($data)) {
    $usernm = isset($data->username) ? $data->username : '';
    $pass = isset($data->password) ? $data->password : '';
}

// query database for a user with that username
$stmt = $user->readOneRaw(strtolower($usernm));
$num = $stmt->rowCount();

// check if more than 0 records found
if($num>0){
 
    // products array
    $takings_arr=array();
    $takings_arr["records"]=array();

    // take just the first row
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // extract row
    // this will make $row['name'] to
    // just $name only
    extract($row);

    if ($suspended || $failedloginattempts > 4) {
        http_response_code(401);
        echo json_encode(
            array("message" => "User is suspended.")
        );
    }
    else if (password_verify($pass, $password)){

        $user->id = $id;
        $user->username = $username;
        $user->role = $role;
        $user->firstname = $firstname;
        $user->surname = $surname;
        
        // Create a new access and refresh JWT pair, with claims of username and isAdmin  
        // Suspended is not a claim because you can't get to this point if user is suspended
        $jwt = new \Models\JWTWrapper();
        $user_with_token = $jwt->getUserWithAccessToken($user);
        $jwt->setRefreshTokenCookieFor($user_with_token);

        $user->updateFailedAttempts($id, 0, false);

        echo json_encode($user_with_token);
    }
    else{
        $failedloginattempts++;

        $user->updateFailedAttempts($id, $failedloginattempts, ($failedloginattempts>=$numberPasswordAttempts));

        http_response_code(401);
        echo json_encode(
            array("message" => "Unable to validate that username and password.")
        );
    }

}
else{
    http_response_code(401);
    echo json_encode(
        array("message" => "Unable to validate that username and password.")
    );
}
