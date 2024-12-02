<?php

namespace Models;

use \PDO;

/**
 * Holds QB token information and has data persistance capability.
 * 
 * The QB token is an OAuth2 access/refresh token pair that can be used to perform QuickBooks tasks.
 * There is a different token for each realm: one for the Charity and one for Enterprises.
 * 
 * An App can have links to multiple realms but it can only have one link to each realm.
 * 
 * Only the QB Company Admin can create an app<->realm link.
 * 
 * @category Model
 */
class QuickbooksToken{
    /**
     * Database connection
     * @var Database
     */ 
    private $conn;
    /**
     * The name of the table that holds the data
     * @var string
     */
    private $table_name = "qbtoken";

    /**
     * initializes a new instance of the QuickbooksToken class.
     */
    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    /**
     * The ID of the user in the aukw database
     * @var int
     */
    public $userid;
    /**
     * The email address of the QB user that created the link
     * @var string
     */
    public $email;
    /**
     * The full name of the QB user that created the link
     * @var string
     */
    public $fullname;
    /**
     * QBO realm ID, aka company ID
     * @var string
     */
    public $realmid;
    /**
     * The name of the QBO company
     * @var string
     */
    public $companyname;
    /**
     * QBO access token
     * @var string
     */
    public $accesstoken;
    /**
     * Date and time that QBO access token expires in string format. Time is 
     * London local time.
     * @var string
     */
    public $accesstokenexpiry;
    /**
     * QBO access token
     * @var string
     */
    public $refreshtoken;
    /**
     * Date and time that QBO refresh token expires in string format. Time is 
     * London local time.
     * @var string
     */
    public $refreshtokenexpiry;

    /**
     * Insert the QB token information into the database with the values of
     * the current instance.
     * 
     * @return bool 'true' if operation succeeded
     */
    function insert(){
        $query = "INSERT INTO
                    " . $this->table_name . "
                    SET
                    userid=:userid,
                    email=:email,
                    realmid=:realmid,
                    accesstoken=:accesstoken, 
                    accesstokenexpiry=:accesstokenexpiry,
                    refreshtoken=:refreshtoken,
                    refreshtokenexpiry=:refreshtokenexpiry,
                    `timestamp`=NULL
                    ;";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // bind values
        $stmt->bindParam(":userid", $this->userid, PDO::PARAM_INT);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":realmid", $this->realmid);
        $stmt->bindParam(":accesstoken", $this->accesstoken);
        $stmt->bindParam(":accesstokenexpiry", $this->accesstokenexpiry);
        $stmt->bindParam(":refreshtoken", $this->refreshtoken);
        $stmt->bindParam(":refreshtokenexpiry", $this->refreshtokenexpiry);      

        // execute query
        if($stmt->execute()){
            return true;
        }
        
        return false;
    }

    /**
     * Update the QB token information in the database with the values of
     * the current instance.
     * 
     * @return bool 'true' if operation succeeded
     */
    function update(){
        $query = "UPDATE
                    " . $this->table_name . "
                    SET                
                    userid=:userid,
                    email=:email,  
                    accesstoken=:accesstoken, 
                    accesstokenexpiry=:accesstokenexpiry,
                    refreshtoken=:refreshtoken,
                    refreshtokenexpiry=:refreshtokenexpiry,
                    `timestamp`=NULL
                    WHERE realmid=:realmid";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // bind values
        $stmt->bindParam(":userid", $this->userid, PDO::PARAM_INT);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":realmid", $this->realmid);
        $stmt->bindParam(":accesstoken", $this->accesstoken);
        $stmt->bindParam(":accesstokenexpiry", $this->accesstokenexpiry);
        $stmt->bindParam(":refreshtoken", $this->refreshtoken);
        $stmt->bindParam(":refreshtokenexpiry", $this->refreshtokenexpiry);        

        // execute query
        if($stmt->execute()){
            return true;
        }
        
        return false;
    }

    /**
     * Refresh the instance properties with QB token information from the database
     * 
     * @return void Output is echo'd directly to response
     */
    function read($realmid){
        $query = "SELECT t.`accesstoken`,t.`accesstokenexpiry`,t.`refreshtoken`,t.`refreshtokenexpiry`
                        ,t.userid, t.realmid, q.companyName, t.email
                        ,CONCAT(u.firstname, ' ', u.surname) as fullname
                    FROM " . $this->table_name . " t JOIN qbrealm q ON t.realmid = q.realmid" .
                    " JOIN user u ON t.userid = u.id " .
                    " WHERE t.realmid=:realmid";

        // prepare query
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":realmid", $realmid);   

        // execute query
        $stmt->execute();

        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // set values to object properties
        if ( !empty($row) ) {
            $this->userid = $row['userid'];
            $this->email = $row['email'];
            $this->fullname = $row['fullname'];
            $this->realmid = $row['realmid'];
            $this->companyname = $row['companyName'] ?? '';
            $this->accesstoken = $row['accesstoken'];
            $this->accesstokenexpiry = $row['accesstokenexpiry'];
            $this->refreshtoken = $row['refreshtoken'];
            $this->refreshtokenexpiry = $row['refreshtokenexpiry'];
        }
    }

    /**
     * Get a list of all the saved QB credentials in the database
     * 
     * @return QuickbooksToken[] Array of access and refresh tokens
     */
    function read_all(){
        
        $query = "SELECT t.`accesstoken`,t.`accesstokenexpiry`,t.`refreshtoken`,t.`refreshtokenexpiry`
                        ,t.userid, t.realmid, q.companyName, t.email
                        ,CONCAT(u.firstname, ' ', u.surname) as fullname
                    FROM " . $this->table_name . " t JOIN qbrealm q ON t.realmid = q.realmid" .
                    " JOIN user u ON t.userid = u.id ";
        
        // prepare query
        $stmt = $this->conn->prepare($query);
   
        // execute query
        $stmt->execute();

        $num = $stmt->rowCount();

        $item_arr=array();

        if ($num>0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
                $item_arr[] = array(
                    "userid" => $userid,
                    "email" => $email,
                    "fullname" => $fullname,
                    "realmid" => $realmid,
                    "companyname" => $companyName ?? '',
                    "accesstoken" => $accesstoken,
                    "accesstokenexpiry" => $accesstokenexpiry,
                    "refreshtoken" => $refreshtoken,
                    "refreshtokenexpiry" => $refreshtokenexpiry
                );
            }            
        }

        return $item_arr;
    }

    /**
     * Delete the QB access and refresh tokens from the database
     * 
     * @param string $realmid QBO Company id
     * @return bool 'true' if operation succeeded
     */
    public function delete($realmid){
        $query = "DELETE FROM " . $this->table_name . 
            " WHERE realmid=:realmid";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":realmid", $realmid);

        // execute query
        if($stmt->execute()){
            return true;
        }

        return false;
    }


}