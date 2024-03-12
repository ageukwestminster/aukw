<?php

namespace Models;

use \PDO;

/**
 * Holds QB token information and has data persistance capability.
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
     * The ID of the user
     * @var int
     */
    public $userid;
    /**
     * QBO realm ID, aka company ID
     * @var string
     */
    public $realmid;
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
                    accesstoken=:accesstoken, 
                    accesstokenexpiry=:accesstokenexpiry,
                    refreshtoken=:refreshtoken,
                    refreshtokenexpiry=:refreshtokenexpiry,
                    `timestamp`=NULL
                    WHERE
                        userid=:userid AND realmid=:realmid";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // bind values
        $stmt->bindParam(":userid", $this->userid, PDO::PARAM_INT);
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
    function read($userid, $realmid){
        $query = "SELECT `accesstoken`,`accesstokenexpiry`,`refreshtoken`,`refreshtokenexpiry`
                        ,userid,realmid
                    FROM " . $this->table_name . 
                    " WHERE userid=:userid AND realmid=:realmid";
        
        // prepare query
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userid", $userid, PDO::PARAM_INT);
        $stmt->bindParam(":realmid", $realmid);

        // execute query
        $stmt->execute();

        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // set values to object properties
        if ( !empty($row) ) {
            $this->userid = $row['userid'];
            $this->realmid = $row['realmid'];
            $this->accesstoken = $row['accesstoken'];
            $this->accesstokenexpiry = $row['accesstokenexpiry'];
            $this->refreshtoken = $row['refreshtoken'];
            $this->refreshtokenexpiry = $row['refreshtokenexpiry'];
        }
    }

    /**
     * Refresh the instance properties with QB token information from the database
     * 
     * @return QuickbooksToken[] Array of 
     */
    function read_all($userid){
        $query = "SELECT t.`accesstoken`,t.`accesstokenexpiry`,t.`refreshtoken`,t.`refreshtokenexpiry`
                        ,t.userid,t.realmid, q.companyName
                    FROM " . $this->table_name . " t JOIN qbrealm q ON t.realmid = q.realmid" .
                    " WHERE userid=:userid";
        
        // prepare query
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userid", $userid, PDO::PARAM_INT);

        // execute query
        $stmt->execute();

        $num = $stmt->rowCount();

        $item_arr=array();

        if ($num>0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
                $item_arr[] = array(
                    "userid" => $userid,
                    "realmid" => $realmid,
                    "companyName" => $companyName ?? '',
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
     * @return bool 'true' if operation succeeded
     */
    public function delete(){
        $query = "DELETE FROM " . $this->table_name . 
            " WHERE userid=:userid AND realmid=:realmid";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userid", $this->userid, PDO::PARAM_INT);
        $stmt->bindParam(":realmid", $this->realmid);

        // execute query
        if($stmt->execute()){
            return true;
        }

        return false;
    }


}