<?php

namespace Models;

use PDO;

/**
 * Factory class that provides a method to query Rules
 *
 * @category Model
 */
class Rules
{
    /**
     * Database connection
     * @var PDO|null
     */
    private $conn;
    /**
     * The name of the table that holds the data
     * @var string
     */
    private $table_name = "rule";
    /**
     * The primary key field name of the table that holds the data
     * @var string
     */
    private $table_id = "ruleid";

    /**
     * The ruleid of a single Rule.
     *
     * @var int
     */
    protected int $id;

    /**
     * ID setter
     */
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * ID getter.
     */
    public function getId(): string
    {
        return $this->id;
    }


    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->conn = \Core\Database::getInstance()->conn;
    }

    /**
     * Static constructor / factory
     */
    public static function getInstance()
    {
        return new self();
    }


    /**
   * Return an array of rules
   * @return array Returns an array
   */
    public function read(): array
    {

        $query = "SELECT
      " . $this->table_id ." as `id`, `name`, `charity`, `search_account`, 
      `search_entity`, `search_docnumber`, `search_memo`, `trade_account`, 
      `trade_entity`, `trade_taxable`, `trade_description`, `trade_memo`
    FROM
        " . $this->table_name . "
    ORDER BY ". $this->table_id;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $num = $stmt->rowCount();

        $items = array();

        // check if more than 0 records found
        if ($num > 0) {

            // retrieve our table contents
            // fetch() is faster than fetchAll()
            // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // extract row
                // this will make $row['name'] to
                // just $name only
                extract($row);

                $items[] = array(
                        "id" => $id,
                        "name" => $name,
                        "charity" => $charity,
                        "search_account" => $search_account,
                        "search_entity" => $search_entity,
                        "search_docnumber" => $search_docnumber,
                        "search_memo" => $search_memo,
                        "account" => $trade_account,
                        "entity" => $trade_entity,
                        "taxable" => $trade_taxable,
                        "description" => $trade_description,
                        "memo" => $trade_memo,
                    );
            }
        }

        return $items;

    }


}
