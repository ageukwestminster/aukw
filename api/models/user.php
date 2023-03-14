<?php

namespace Models;

use \PDO;

class User{
    // database conn 
    private $conn;
    // table name
    private $table_name = "user";

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

    // used by select drop-down list
    public function read(){

        //select all data
        $query = "SELECT
                    u.`id`, u.`username`, u.`password`, u.`shopid`,
                    u.isAdmin, u.suspended, u.`firstname`, u.failedloginattempts, 
                    u.`email`, u.`title`, u.`surname`
                    FROM
                    " . $this->table_name . " u " . 
                    (isset($this->suspended)?'WHERE suspended = '.$this->suspended.' ':'');                    

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

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
                        "firstname" => html_entity_decode($firstname),
                        "surname" => html_entity_decode($surname),
                        "role" => $isAdmin?'Admin':'User',
                        "suspended" => $suspended?true:false,
                        "email" => html_entity_decode($email),
                        "title" => html_entity_decode($title),
                        "shopid" => $shopid
                    );
        
                    // create nonindexed array
                    array_push ($users_arr, $user_item);
                }
               
        }

        return $users_arr;
    }

    // find the details of one user using $id
    public function readOne(){

        //select all data
        $query = "SELECT
                    u.`id`, u.`username`, u.`password`, u.`shopid`,
                    u.isAdmin, u.suspended, u.`firstname`, u.`failedloginattempts`,
                    u.`email`, u.`title`, u.`surname`
                    FROM
                    " . $this->table_name . " u
                    WHERE u.id = :id
                    LIMIT 0,1";
                
        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind id of product to be updated
        $id = filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam (":id", $id, PDO::PARAM_INT);

        // execute query
        $stmt->execute();

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
        }
    }

    // find the details of one user using $username
    public function readOneRaw($username){

        //select all data
        $query = "SELECT
                    u.`id`, u.`username`, u.`password`, u.`surname`, u.`shopid`,
                    u.isAdmin, u.suspended, u.`firstname`, u.`failedloginattempts`,
                    CASE WHEN u.isAdmin THEN 'Admin' ELSE 'User' END as `role`,
                    u.`email`, u.`title`
                    FROM
                    " . $this->table_name . " u
                    WHERE username = ?
                    LIMIT 0,1";
                
        $stmt = $this->conn->prepare( $query );
        $stmt->bindParam(1, $username);
        $stmt->execute();

        return $stmt;
    }

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
                    failedloginattempts=:failedloginattempts
                    " . (isset($this->password)?',password=:password ':'');
        
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
                    failedloginattempts=:failedloginattempts
                    " . (isset($this->password)?',password=:password ':'') ."
                 WHERE
                    id=:id";
        
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
        if(isset($this->password)) {
            $this->password=htmlspecialchars(strip_tags($this->password));
            $stmt->bindParam(":password", $this->password);
        }
        
        $this->failedloginattempts = !empty($this->failedloginattempts) ? $this->failedloginattempts : 0;

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

    function delete(){
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

    function updateFailedAttempts($id, $failedloginattempts, bool $suspended){
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

    
    public function checkPassword($pwd, &$errors) {
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
}