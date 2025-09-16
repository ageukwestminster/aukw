<?php

namespace Models;

use PDO;

/**
 * Defines a user's usertoken. Can be an access or refresh token.
 * 
 * @category Model
 */
class UserToken{
    /**
     * Database connection
     * @var PDO|null
     */ 
    private $conn;
    /**
     * The name of the table that holds the data
     * @var string
     */
    private $table_name = "usertoken";

    /**
     * Instantiate a new UserToken
     */
    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    /**
     * Store the identifiers of the tokens (not the tokens themselves) into the database.
     *
     * @param int $iduser The id of the user
     * @param string $accessTokenJti Unique identifier for the access token. Usuallly a GUID.
     * @param string $refreshTokenJti Unique identifier for the refresh token. Usuallly a GUID.
     * @param bool $status When 'false' the refresh token is invalid.
     * @param mixed $expiresAt Expiry of refresh token.
     * 
     * @return bool 'true' on success, 'false' otherwise
     * 
     */
    function store(int $iduser, string $accessTokenJti, string $refreshTokenJti
                    ,bool $status, $expiresAt){
        $query = "INSERT INTO
                    " . $this->table_name . "
                    SET 
                    iduser=:iduser,
                    primaryKey=:primaryKey, 
                    secondaryKey=:secondaryKey,
                    status=:status,
                    expiresAt=:expiresAt
                    ";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // bind values
        $stmt->bindParam(":iduser", $iduser, PDO::PARAM_INT);
        $stmt->bindParam(":primaryKey", $accessTokenJti);
        $stmt->bindParam(":secondaryKey", $refreshTokenJti);
        $stmt->bindParam(":status", $status, PDO::PARAM_INT);
        $stmt->bindParam(":expiresAt", $expiresAt);      

        // execute query
        if($stmt->execute()){
            return true;
        }
        
        return false;
    }

    /**
     * Update the database valid/invalid flag for the user's token
     * identified by $hash. Can be an access or refresh token.
     * 
     * @param int $iduser The userid of the user
     * @param string $hash The cryptographic hash of the token
     * @param bool $isValid 'True' means set token to valid, 'false' means set it to invalid
     * 
     * @return bool If database update succeeds then return true, else false.
     */
    public function updateStatus($iduser, $hash, $isValid){
        $query = "UPDATE
                    " . $this->table_name . "
                    SET 
                    status=:status
                 WHERE
                    iduser=:id AND 
                        (primaryKey=:hash1 OR secondaryKey=:hash2)";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // bind values
        $status = $isValid?1:0;
        $stmt->bindParam(":status", $status, PDO::PARAM_INT);    
        $stmt->bindParam(":id", $iduser, PDO::PARAM_INT);    
        $stmt->bindParam(":hash1", $hash, PDO::PARAM_STR);
        $stmt->bindParam(":hash2", $hash, PDO::PARAM_STR);

        // execute query
        if($stmt->execute()){
            return true;
        }
        
        return false;
    }

    function getAccessTokenStatus($iduser, $key){
        $query = "SELECT status FROM
                    " . $this->table_name . "
                 WHERE
                    iduser=:id AND primaryKey=:primaryKey";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // bind values        
        $stmt->bindParam(":id", $iduser, PDO::PARAM_INT);    
        $stmt->bindParam(":primaryKey", $key);    

        // execute query
        $stmt->execute();
        
        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // set values to object properties
        if ( !empty($row) ) {
            return $row['status'] == 0 ? false : true;
        } else {
            return false;
        }
    }

    function getRefreshTokenStatus($iduser, $key){
        $query = "SELECT status FROM
                    " . $this->table_name . "
                 WHERE
                    iduser=:id AND secondaryKey=:secondaryKey";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // bind values        
        $stmt->bindParam(":id", $iduser, PDO::PARAM_INT);    
        $stmt->bindParam(":secondaryKey", $key);    

        // execute query
        $stmt->execute();
        
        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // set values to object properties
        if ( !empty($row) ) {
            return $row['status'] == 0 ? false : true;
        } else {
            return false;
        }
    }

    function deleteAll($iduser){
        $query = "DELETE FROM " . $this->table_name . " WHERE iduser = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $iduser, PDO::PARAM_INT);

        // execute query
        if($stmt->execute()){
            return true;
        }

        return false;
    }

}