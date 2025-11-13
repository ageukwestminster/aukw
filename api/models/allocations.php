<?php

namespace Models;

use PDO;
use Exception;

/**
 * Factory class that provides a method to query Allocations
 *
 * @category Model
 */
class Allocations
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
    private $table_name = "allocation";


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
    public function read(?int $payrollNumber = null): array
    {
        $query = "SELECT " .
          " `quickbooksId`, `payrollNumber`, `percentage`, `account`, `class`, `isShopEmployee`
    FROM
        " . $this->table_name .
          ($payrollNumber !== null ? " WHERE payrollNumber = :_payrollNumber " : "") .
        " ORDER BY quickbooksId";

        $stmt = $this->conn->prepare($query);
        if ($payrollNumber !== null) {
            $stmt->bindParam(":_payrollNumber", $payrollNumber, PDO::PARAM_INT);
        }
        $stmt->execute();

        $num = $stmt->rowCount();

        $items = array();

        // check if more than 0 records found
        if ($num > 0) {
            $id = 1;
            // retrieve our table contents
            // fetch() is faster than fetchAll()
            // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // extract row
                // this will make $row['name'] to
                // just $name only
                extract($row);

                $items[] = array(
                  "id" => $id++,
                  "quickbooksId" => $quickbooksId,
                  "payrollNumber" => $payrollNumber,
                  "percentage" => $percentage,
                  "account" => $account,
                  "class" => $class,
                  "isShopEmployee" => $isShopEmployee ? true : false
                );
            }
        }

        return $items;

    }

    /**
     * Delete all the allocations from the database.
     *
     * @return bool 'true' if database delete succeeded.
     *
     */
    public function delete(): bool
    {
        // MySQL stored procedure
        $query = "CALL delete_allocations()";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }

    /**
     * Delete from the database all the Allocations for a given payroll number.
     *
     * @return bool 'true' if database delete succeeded.
     *
     */
    public function deleteByPayrollNumber(int $payrollNumber): bool
    {
        // MySQL stored procedure
        $query = "CALL delete_allocations_for_employee(:_payrollNumber)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":_payrollNumber", $payrollNumber, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Check that each employee has employee allocation percentages summing to 100%
     *
     * @return bool 'true' if sum of employee percentages is always exactly 100%
     *
     */
    public function verify(): bool
    {
        // MySQL stored procedure
        $query = "SELECT quickbooksId, payrollNumber,SUM(percentage)
                    FROM allocation
                    GROUP BY quickbooksId, payrollNumber
                    HAVING SUM(percentage) <> 100";
        $stmt = $this->conn->prepare($query);

        $stmt->execute();
        return $stmt->rowCount() == 0;
    }
}
