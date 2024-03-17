<?php
namespace Models;
use \PDO;
/**
 * Defines a QBRealm and has data persistance capbility.
 * 
 * @category Model
 */
class QBRealm{
    /**
     * Database connection
     * @var PDO|null
     */ 
    private $conn;
    /**
     * The name of the table that holds the data
     * @var string
     */
    private $table_name = "qbrealm";
    /**
     * The primary key field name of the table that holds the data
     * @var string
     */
    private $table_id = "realmid";

    /**
     * The realm ID is a unique ID value which identifies a specific QuickBooks Online company.
     * @var string
     */
    public $realmid;
    /**
     * The name of the specific QuickBooks Online company.
     * @var string
     */
    public $name;
    /**
     * 'True' if the specific QuickBooks Online company is in tthe QBO sandbox.
     * @var bool
     */
    public $issandbox;

    /**
     * initializes a new instance of the Shop class.
     */
    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    /**
     * Get a list of all QB Realms that we can enter takings for. Used by select drop-down list.
     * Ignores whether company is sandbox or not
     * 
     * @return array All available QB realms
     */
    public function read(bool $includeSandboxCompanies){

        //select all data
        $query = "SELECT
                    " . $this->table_id ." as `realmid`, `companyName` as `name`, issandbox
                FROM
                    " . $this->table_name;
        if (!$includeSandboxCompanies) {
            $query .= " WHERE issandbox = 0 ";
        }
        $query .= " ORDER BY ". $this->table_id;

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        $num = $stmt->rowCount();

        $shops=array();

        // check if more than 0 record found
        if($num>0){

            // retrieve our table contents
            // fetch() is faster than fetchAll()
            // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                // extract row
                // this will make $row['name'] to
                // just $name only
                extract($row);
            
                $shops[]=array(
                    "realmid" => html_entity_decode($realmid ?? ''),
                    "name" => html_entity_decode($name ?? ''),
                    "issandbox" => $issandbox?true:false,
                    );
                }
        }

        return $shops;
    }
        
    /**
     * Get data of a single QB Realm that is specified by either id or name.
     * 
     * @return array The reuired shop.
     */
    public function readOne(){

        //select data for one item using PK of table
        $query = "SELECT
                    " . $this->table_id ." as `realmid`, `companyName` as `name`, issandbox
                FROM
                    " . $this->table_name . "
                    WHERE "; 

        // WHERE clause depends on parameters
        if($this->name) {
            $query .= "LOWER(name) LIKE :name ";
        }            
        else {
            $query .= $this->table_id ." = :id ";
        }
        $query .= "LIMIT 0,1";

        // prepare query statement
        $stmt = $this->conn->prepare($query);      

        if($this->name) {
            $name = htmlspecialchars(strip_tags($this->name)).'%';
            $stmt->bindParam (":name", $name, PDO::PARAM_STR);
        }
        else {
            $id = htmlspecialchars(strip_tags($this->realmid));
            $stmt->bindParam (":id", $id, PDO::PARAM_STR);
        }
        
        $stmt->execute();

        if (!$stmt->rowCount()) {
            return array();
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->realmid = html_entity_decode($row['realmid'] ?? '');
        $this->name = html_entity_decode($row['name'] ?? '');
        $this->issandbox = $row['issandbox']?true:false;
        
        return array(
            "id" => $this->realmid,
            "name" => $this->name,
            "issandbox" => $this->issandbox,
        );
    }
}