<?php
namespace Models;
use \PDO;
/**
 * Defines a shop and has data persistance capbility.
 * 
 * @category Model
 */
class Shop{
    /**
     * Database connection
     * @var PDO|null
     */ 
    private $conn;
    /**
     * The name of the table that holds the data
     * @var string
     */
    private $table_name = "shop";
    /**
     * The primary key field name of the table that holds the data
     * @var string
     */
    private $table_id = "id";

    /**
     * The id of the shop
     * @var int
     */
    public $id;
    /**
     * The name of the shop
     * @var string
     */
    public $name;

    /**
     * initializes a new instance of the Shop class.
     */
    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    /**
     * Get a list of all shops that we can enter takings for. Used by select drop-down list.
     * Ignores whether shop is open or closed.
     * 
     * @return array All available shops
     */
    public function read(){

        //select all data
        $query = "SELECT
                    " . $this->table_id ." as `id`, `name`
                FROM
                    " . $this->table_name . "
                ORDER BY ". $this->table_id;

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        $num = $stmt->rowCount();

        $shops=array();

        // check if more than 0 records found
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
                        "id" => $id,
                        "name" => $name
                    );
                }
        }

        return $shops;
    }
        
    /**
     * Get data of a single shop that is specified by either id or name.
     * 
     * @return array The reuired shop.
     */
    public function readOne(){

        //select data for one item using PK of table
        $query = "SELECT
                    " . $this->table_id ." as `id`, `name`
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
            $id = filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);
            $stmt->bindParam (":id", $id, PDO::PARAM_INT);
        }
        
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->id = $row['id'];
        $this->name = $row['name'];
        
        return array(
            "id" => $this->id,
            "name" => $this->name    
        );
    }
}