<?php

namespace Models;

use \PDO;

class QuickbooksToken{
    // database conn 
    private $conn;
    // table name
    private $table_name = "qbtoken";

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;

        //$jwt = new JWTWrapper();

        //$this->iduser = $jwt->id;
    }

    public $iduser;
    public $accesstoken;
    public $accesstokenexpiry;
    public $refreshtoken;
    public $refreshtokenexpiry;

    function insert(){
        $query = "INSERT INTO
                    " . $this->table_name . "
                    SET 
                    iduser=:iduser,
                    accesstoken=:accesstoken, 
                    accesstokenexpiry=:accesstokenexpiry,
                    refreshtoken=:refreshtoken,
                    refreshtokenexpiry=:refreshtokenexpiry,
                    `timestamp`=NULL
                    ;";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // bind values
        $stmt->bindParam(":iduser", $this->iduser, PDO::PARAM_INT);
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
                    iduser=:iduser;";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // bind values
        $stmt->bindParam(":iduser", $this->iduser, PDO::PARAM_INT);
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

    function read(){
        $query = "SELECT `iduser`,`accesstoken`,`accesstokenexpiry`,`refreshtoken`,`refreshtokenexpiry`
                    FROM " . $this->table_name . "
                    WHERE
                    iduser=:iduser;";
        
        // prepare query
        $stmt = $this->conn->prepare($query);

        // bind values
        $stmt->bindParam(":iduser", $this->iduser, PDO::PARAM_INT);

        // execute query
        $stmt->execute();

        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // set values to object properties
        if ( !empty($row) ) {
            $this->iduser = $row['iduser'];
            $this->accesstoken = $row['accesstoken'];
            $this->accesstokenexpiry = $row['accesstokenexpiry'];
            $this->refreshtoken = $row['refreshtoken'];
            $this->refreshtokenexpiry = $row['refreshtokenexpiry'];
        }
    }

    function delete(){
        $query = "DELETE FROM " . $this->table_name . " WHERE iduser = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->iduser, PDO::PARAM_INT);

        // execute query
        if($stmt->execute()){
            return true;
        }

        return false;
    }

}