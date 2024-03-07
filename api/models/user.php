<?php

namespace Models;

use \PDO;

/**
 * Defines a user and has data persistance capbility.
 * 
 * @category Model
 */
class User{
    /**
     * Database connection
     * @var PDO|null
     */ 
    private $conn;
    /**
     * The name of the table that holds the data
     * @var string
     */
    private $table_name = "user";

    /**
     * Instantiate a new User
     */
    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    // object properties
    public $id;
    public $username;
    public $role;
    public $email;
    public $suspended;
    public $password;
    public $firstname;
    public $surname;
    public $shopid;
    public $title;
    public $failedloginattempts;
    public $quickbooksUserId;


    /**
     * Return details of all Users
     * 
     * @return array An array of Users
     */
    public function read(){
               
        $stmt = User::prepareAndExecuteSelectStatement('BY_SUSPENDED');

        $num = $stmt->rowCount();

        $users_arr=array();

        if($num>0){
        
            // retrieve our table contents
            // fetch() is faster than fetchAll()
            // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                // extract row
                // this will make $row['name'] to
                // just $name only
                extract($row);
            
                    $user_item=array(
                        "id" => $id,
                        "username" => $username,
                        "firstname" => html_entity_decode($firstname ?? ''),
                        "surname" => html_entity_decode($surname ?? ''),
                        "role" => $isAdmin?'Admin':'User',
                        "suspended" => $suspended?true:false,
                        "email" => html_entity_decode($email ?? ''),
                        "title" => html_entity_decode($title ?? ''),
                        "shopid" => $shopid,
                        "quickbooksUserId" => html_entity_decode($quickbooksUserId ?? ''),
                    );
        
                    // create nonindexed array
                    array_push ($users_arr, $user_item);
                }
               
        }

        return $users_arr;
    }

    /**
     * Retrieve from the database details of the User, specified by
     * First Name, Surname and Email address
     * 
     * @return void
     * 
     */
    public function readOneByNameAndEmail(){

        $stmt = User::prepareAndExecuteSelectStatement('BY_NAMEANDEMAIL');

        $this->transferPropertiestoModel($stmt);

    }

    /**
     * Query for the details of one user using $username but return the results as a 
     * MySQLi statement rather than a JSON string or an object
     * 
     * @return object Returns a MySQLi statement
     */
    public function readOneByUsername(){
        return User::prepareAndExecuteSelectStatement('BY_USERNAME');
    }

    /**
     * Retrieve from the database details of a User, queried using the 
     * model property $id
     * 
     * @return void
     * 
     */
    public function readOneByUserID(){

        // execute query
        $stmt = User::prepareAndExecuteSelectStatement('BY_USERID');

        $this->transferPropertiestoModel($stmt);
    }


    /**
     * Add a new User to the database.
     * 
     * @return bool 'true' if database insert succeeded.
     * 
     */
    function create(){
        $query = "INSERT INTO
                    " . $this->table_name . "
                    SET 
                    username=:username,
                    isAdmin=:isadmin, 
                    firstname=:firstname,
                    surname=:surname,
                    shopid=:shopid,
                    email=:email,
                    title=:title,
                    suspended=:suspended,
                    failedloginattempts=:failedloginattempts"
                    . (isset($this->password)?',password=:password ':'');
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->username=htmlspecialchars(strip_tags($this->username));
        $this->firstname=htmlspecialchars(strip_tags($this->firstname));
        $this->surname=htmlspecialchars(strip_tags($this->surname));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->title=htmlspecialchars(strip_tags($this->title));
        $this->role=htmlspecialchars(strip_tags($this->role));
        $this->failedloginattempts=filter_var($this->failedloginattempts, FILTER_SANITIZE_NUMBER_INT);
        $this->shopid=filter_var($this->shopid, FILTER_SANITIZE_NUMBER_INT);
        $this->role=htmlspecialchars(strip_tags($this->role));

        $isadmin = ($this->role=='Admin') ? 1 : 0;
        $suspended = $this->suspended ? 1 : 0;

        // bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":isadmin", $isadmin, PDO::PARAM_INT);
        $stmt->bindParam(":suspended", $suspended, PDO::PARAM_INT);
        $stmt->bindParam(":firstname", $this->firstname);
        $stmt->bindParam(":surname", $this->surname);
        $stmt->bindParam(":shopid", $this->shopid, PDO::PARAM_INT);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":failedloginattempts", $this->failedloginattempts, PDO::PARAM_INT);
        $stmt->bindParam(":password", $this->password);
        
        // execute query
        if($stmt->execute()){
            $this->id = $this->conn->lastInsertId();
            if($this->id) {
                return true;
            } else {
                return false;
            }
        }
        
        return false;
    }

    /**
     * Update an existing User in the database with new data.
     * 
     * @return bool 'true' if database update succeeded.
     * 
     */
    function update(){
        $query = "UPDATE
                    " . $this->table_name . "
                    SET 
                    username=:username,
                    isAdmin=:isadmin, 
                    suspended=:suspended,
                    email=:email,
                    title=:title,
                    firstname=:firstname,
                    surname=:surname,
                    shopid=:shopid,
                    timestamp=NULL,
                    failedloginattempts=:failedloginattempts"
                    . (isset($this->quickbooksUserId)?',quickbooksUserId=:quickbooksUserId ':'') 
                    . (isset($this->password)?',password=:password ':'') 
                    ." WHERE id=:id";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->username=htmlspecialchars(strip_tags($this->username));
        $this->firstname=htmlspecialchars(strip_tags($this->firstname));
        $this->surname=htmlspecialchars(strip_tags($this->surname));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->title=htmlspecialchars(strip_tags($this->title));
        $this->role=htmlspecialchars(strip_tags($this->role));
        $this->shopid=filter_var($this->shopid, FILTER_SANITIZE_NUMBER_INT);

        $this->failedloginattempts=filter_var($this->failedloginattempts, FILTER_SANITIZE_NUMBER_INT);
        $this->failedloginattempts = !empty($this->failedloginattempts) ? $this->failedloginattempts : 0;

        if(isset($this->password)) {
            $this->password=htmlspecialchars(strip_tags($this->password));
            $stmt->bindParam(":password", $this->password);
        }
        if(isset($this->quickbooksUserId)) {
            $this->quickbooksUserId=htmlspecialchars(strip_tags($this->quickbooksUserId));
            $stmt->bindParam(":quickbooksUserId", $this->quickbooksUserId);
        }
        

        $isadmin = ($this->role=='Admin') ? 1 : 0;
        $suspended = $this->suspended ? 1 : 0;        

        // bind values
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":isadmin", $isadmin, PDO::PARAM_INT);
        $stmt->bindParam(":suspended", $suspended, PDO::PARAM_INT);
        $stmt->bindParam(":firstname", $this->firstname);
        $stmt->bindParam(":surname", $this->surname);
        $stmt->bindParam(":shopid", $this->shopid, PDO::PARAM_INT);   
        $stmt->bindParam(":failedloginattempts", $this->failedloginattempts, PDO::PARAM_INT); 

        // execute query
        if($stmt->execute()){
            return true;
        }
        
        return false;
    }

    /**
     * Delete the user from the database that matches the id property 
     * of the user.
     * 
     * @return bool 'true' if database delete succeeded.
     * 
     */
    public function delete(){
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $this->id=filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT);

        // execute query
        if($stmt->execute()){
            return true;
        }

        return false;
    }


    /**
     * Update 2 fields of the user: failedloginattempts and suspended.
     *
     * @param int $id The id of the user to update.
     * @param int $failedloginattempts The number of failed attempts to login.
     * @param bool $suspended If 'true' then the user is suspended.
     * 
     * @return bool 'true' if database update succeeded.
     * 
     */
    public function updateFailedAttempts(int $id, int $failedloginattempts, bool $suspended){
        $query = "UPDATE
                    " . $this->table_name . "
                    SET 
                    failedloginattempts=:failedloginattempts,
                    suspended=:suspended
                 WHERE
                    id=:id";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // bind values
        $stmt->bindParam(":id", $id);      
        $stmt->bindParam(":failedloginattempts", $failedloginattempts);     
        $stmt->bindValue(":suspended", $suspended ? 1 : 0);

        // execute query
        if($stmt->execute()){
            return true;
        }
        
        return false;
    }

    
    /**
     * Check the supplied password meets minimum standards:
     *  - 8 or more characters
     *  - Must include at least one number
     *  - Must include ast least one letter
     *
     * @param string $pwd The password to test
     * @param array $errors An array of errors. Empty if no errors.
     * 
     * @return bool 'true' if password passess the tests
     * 
     */
    public function checkPassword(string $pwd, &$errors) {
        $errors_init = $errors;
    
        if (strlen($pwd) < 8) {
            $errors[] = "Password too short!";
        }
    
        if (!preg_match("#[0-9]+#", $pwd)) {
            $errors[] = "Password must include at least one number!";
        }
    
        if (!preg_match("#[a-zA-Z]+#", $pwd)) {
            $errors[] = "Password must include at least one letter!";
        }     
    
        return ($errors == $errors_init);
    }

    /**
     * Build and execute a MySQLi statement to query the database for a user or users. The
     * query is customised by the where query specifier. This method was written to reduce
     * code re-use in the various read... methods.
     * 
     * @param string $whereQuery One of '','BY_USERID', 'BY_USERNAME','BY_NAMEANDEMAIL','BY_SUSPENDED'
     * 
     * @return object Returns a MySQLi statement
     */
    private function prepareAndExecuteSelectStatement(string $whereQuery) {
        
        $query = "SELECT
                    u.`id`, u.`username`, u.`password`, u.`surname`, u.`shopid`,
                    u.isAdmin, u.suspended, u.`firstname`, u.`failedloginattempts`,
                    CASE WHEN u.isAdmin THEN 'Admin' ELSE 'User' END as `role`,
                    u.`email`, u.`title`, u.`quickbooksUserId`
                    FROM
                    " . $this->table_name . " u";
                
        switch ($whereQuery) {
            case 'BY_USERID':
                $query .= " WHERE u.id = :id";
                break;
            case 'BY_USERNAME':
                $query .= " WHERE u.username = :username";
                break;
            case 'BY_NAMEANDEMAIL':
                $query .= " WHERE u.firstname = :firstname AND " . 
                                "u.surname = :surname AND u.email = :email";
                break;                
            case 'BY_SUSPENDED':
                $query .= 
                    (isset($this->suspended)?' WHERE suspended = '.$this->suspended.' ':'');   
                break;            
        }             

        $stmt = $this->conn->prepare( $query );

        switch ($whereQuery) {
            case 'BY_USERID':
                $id = filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);
                $stmt->bindParam (":id", $id, PDO::PARAM_INT);
                break;
            case 'BY_USERNAME':
                $this->username=htmlspecialchars(strip_tags($this->username));
                $stmt->bindParam(":username", $this->username);
                break;
            case 'BY_NAMEANDEMAIL':
                $this->firstname=htmlspecialchars(strip_tags($this->firstname));
                $this->surname=htmlspecialchars(strip_tags($this->surname));
                $this->email=htmlspecialchars(strip_tags($this->email));
                $stmt->bindParam(":firstname", $this->firstname);
                $stmt->bindParam(":surname", $this->surname);
                $stmt->bindParam(":email", $this->email);
                break;                           
        }   

        $stmt->execute();

        return $stmt;
    }

    /**
     * Update the properties of the user model with the data from the database
     * 
     * @return void
     */
    private function transferPropertiestoModel($stmt) {
        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // set values to object properties
        if ( !empty($row) ) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->firstname = $row['firstname'];
            $this->surname = $row['surname'];
            $this->password = $row['password'];
            $this->shopid = $row['shopid'];
            $this->email = $row['email'];
            $this->title = $row['title'];
            $this->role = $row['isAdmin'] ? 'Admin' : 'User';
            $this->suspended = $row['suspended']?true:false;
            $this->failedloginattempts = $row['failedloginattempts'];
            $this->quickbooksUserId = $row['quickbooksUserId'];
        }
    }

}