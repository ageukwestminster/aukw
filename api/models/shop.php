<?php
namespace Models;
use \PDO;
class Shop{
    // database conn 
    private $conn;
    // table name
    private $table_name = "shop";
    private $table_id = "id";

    // object properties
    public $id;
    public $name;

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    // used by select drop-down list
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

        $item_arr=array();

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
            
                    $item=array(
                        "id" => $id,
                        "name" => $name
                    );

                    $item_arr[] = $item;
                }
        }

        return $item_arr;
    }
        
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

            // get retrieved row
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // set values to object properties
            $this->id = $row['id'];
            $this->name = $row['name'];
            // create array
            $item = array(
                "id" => $this->id,
                "name" => $this->name    
            );

            return $item;
        }
}