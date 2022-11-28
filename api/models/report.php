<?php

namespace Models;

use \PDO;

class Report{

    private $conn;
    public $startdate;
    public $enddate;
    public $shopID;

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

     // Return an array of net sales data for the last numdatapoints days
     public function dailySalesHistogram(){
        $query = "SELECT UNIX_TIMESTAMP(`date`)*1000 as sales_date,
                    (clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) 
                       as total_after_expenses_and_donations
                    FROM takings t
                    WHERE t.date >= :start AND t.date <= :end" .
                    ($this->shopID ? ' AND t.shopID = :shopID ' : ' ') .
                    "ORDER BY t.`date` ";
        
        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind id of product to be updated
        $stmt->bindParam(":start", $this->startdate);
        $stmt->bindParam(":end", $this->enddate);
        if ($this->shopID) {
            $shopID = filter_var($this->shopID, FILTER_SANITIZE_NUMBER_INT);
            $stmt->bindParam (":shopID", $shopID, PDO::PARAM_INT);
        }

        // execute query
        $stmt->execute();

        $num = $stmt->rowCount();

        $sales_arr=array();
        $sales_arr["average"] = 0;
        $sales_arr["count"] = 0;
        $sales_arr["data"]=array();

        $sum =0; // sum of daily sales as we loop over rows

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
                $sales_arr["count"] = $sales_arr["count"]+1;
                $sum = $sum+$row['total_after_expenses_and_donations'];
                array_push($sales_arr["data"], array($row['sales_date'], $row['total_after_expenses_and_donations']));
            }
        }

        $sales_arr["average"] = round($sum / $sales_arr["count"],2);

        return $sales_arr;
    }
}