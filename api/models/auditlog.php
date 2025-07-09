<?php

namespace Models;

use Exception;
use \PDO;

/**
 * Defines an entry in the Audit Log and has data persistance capbility.
 * 
 * @category Model
 */
class AuditLog{
    /**
     * Database connection
     * @var PDO|null
     */ 
    private $conn;
    /**
     * The name of the table that holds the data
     * @var string
     */
    private $table_name = "auditlog";

    /**
     * Instantiate a new instance of the AuditLog
     */
    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    /**
     * The id of the audit log entry
     * @var int
     */
    public int $id;
    /**
     * The id of the user who performed the auditable action
     * @var int
     */
    public int $userid;
    /**
     * When the action was performed
     * @var string
     */
    public string $timestamp;
    /**
     * The type of action that was performed
     * @var string
     */
    public string $eventtype;
    /**
     * A description of the action
     * @var string
     */
    public string $description;
    /**
     * if the action has involved a CRUD operation on an object then this is the type of object,
     * @var string
     */
    public string $objecttype;
    /**
     * if the action has involved a CRUD operation on an object then this is the database id of the object,
     * @var int
     */
    public int $objectid;


    /**
     * Return details of all audit log entries
     * 
     * @return array An array of audit log entries
     */
    public function read($userid, $startdate, $enddate, $eventtype){
               
        $query = "SELECT
            a.`id`, a.`userid`, u.`username`, CONCAT(u.`firstname`,' ',u.`surname`) as fullname,
            a.eventtype, a.description, a.objecttype, a.objectid, a.timestamp
            FROM
            " . $this->table_name . " a" .
            " JOIN user u ON a.userid = u.id " .
            "WHERE DATE(a.`timestamp`) >= :start AND DATE(a.`timestamp`) <= :end "; 

        if (isset($userid)) {
            $query .= "AND u.id=:userid";
        }

        if (isset($eventtype)) {
            $query .= "AND a.eventtype=:eventtype";
        }

        $query .= " ORDER BY timestamp DESC";

        $stmt = $this->conn->prepare( $query );

        if (isset($userid)) {
            $stmt->bindParam(":userid", $userid, PDO::PARAM_INT);
        }
        if (isset($eventtype)) {
            $stmt->bindParam(":eventtype", $eventtype, PDO::PARAM_STR);
        }
        $stmt->bindParam(":start", $startdate);
        $stmt->bindParam(":end", $enddate);

        $stmt->execute();
        $num = $stmt->rowCount();

        $auditlog_arr=array();

        if($num>0){
        
            // retrieve our table contents
            // fetch() is faster than fetchAll()
            // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                // extract row
                // this will make $row['name'] to
                // just $name only
                extract($row);
            
                    $auditlog_item=array(
                        "id" => $id,
                        "userid" => $userid,
                        "username" => $username,
                        "fullname" => html_entity_decode($fullname ?? ''),
                        "eventtype" => html_entity_decode($eventtype ?? ''),
                        "description" => html_entity_decode($description ?? ''),
                        "objecttype" => html_entity_decode($objecttype ?? ''),
                        "objectid" => html_entity_decode($objectid ?? ''),
                        "timestamp" => $timestamp,
                    );
        
                    // create nonindexed array
                    array_push ($auditlog_arr, $auditlog_item);
                }
               
        }

        return $auditlog_arr;
    }

    function create(){
        $query = "INSERT INTO
                    " . $this->table_name . "
                    SET 
                    userid=:userid,
                    eventtype=:eventtype,
                    description=:description"
                    . (isset($this->objecttype)?',objecttype=:objecttype ':'')
                    . (isset($this->objectid)?',objectid=:objectid ':'')
                    . ",timestamp=NULL;";

        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->userid=htmlspecialchars(strip_tags($this->userid));
        $this->eventtype=htmlspecialchars(strip_tags($this->eventtype));
        $this->description=htmlspecialchars(strip_tags($this->description));

        if(isset($this->objecttype)) {
            $this->objecttype=htmlspecialchars(strip_tags($this->objecttype));
            $stmt->bindParam(":objecttype", $this->objecttype);
        }
        if(isset($this->objectid)) {
            $stmt->bindParam(":objectid", $this->objectid, PDO::PARAM_INT);
        }

        // bind values
        $stmt->bindParam(":userid", $this->userid);
        $stmt->bindParam(":eventtype", $this->eventtype);
        $stmt->bindParam(":description", $this->description);

        // execute query
        if($stmt->execute()){
            $this->id = $this->conn->lastInsertId();
            if($this->id) {
                return true;
            } else {
                throw new Exception("Id of AuditLog entry is missing.");
            }
        }
    }

        /**
     * Return a list of all Event Types
     * 
     * @return array An array of strings
     */
    public function read_eventtypes(){
               
        $query = "SELECT eventtype FROM auditlog GROUP BY eventtype ORDER BY eventtype"; 

        $stmt = $this->conn->prepare( $query );

        $stmt->execute();
        $num = $stmt->rowCount();

        $auditlog_arr=array();

        if($num>0){
        
            // retrieve our table contents
            // fetch() is faster than fetchAll()
            // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                array_push ($auditlog_arr, $row['eventtype']);
            }
               
        }

        return $auditlog_arr;
    }
}