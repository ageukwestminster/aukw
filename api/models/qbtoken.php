<?php

namespace Models;

use \PDO;

/**
 * Holds QB token information and has data persistance capability.
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
                    accesstoken=:accesstoken, 
                    accesstokenexpiry=:accesstokenexpiry,
                    refreshtoken=:refreshtoken,
                    refreshtokenexpiry=:refreshtokenexpiry,
                    `timestamp`=NULL
                    ;";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // bind values
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
                    `timestamp`=NULL";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // bind values
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
     * @return void
     */
    function read(){
        $query = "SELECT `accesstoken`,`accesstokenexpiry`,`refreshtoken`,`refreshtokenexpiry`
                    FROM " . $this->table_name;
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // set values to object properties
        if ( !empty($row) ) {
            $this->accesstoken = $row['accesstoken'];
            $this->accesstokenexpiry = $row['accesstokenexpiry'];
            $this->refreshtoken = $row['refreshtoken'];
            $this->refreshtokenexpiry = $row['refreshtokenexpiry'];
        }
    }

    /**
     * Delete the QB access and refresh tokens from the database
     * 
     * @return bool 'true' if operation succeeded
     */
    public function delete(){
        $query = "DELETE FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);

        // execute query
        if($stmt->execute()){
            return true;
        }

        return false;
    }

}