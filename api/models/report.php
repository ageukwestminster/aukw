<?php

namespace Models;

use \PDO;

/**Used to convert between Unix timestamp and London dates */
use DateTime;
use DateTimeZone;

class Report{

    private $conn;
    public $startdate;
    public $enddate;
    public $shopID;

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

     /**
      * Get data to build a histogram chart from net daily sales. HighCharts date format
      * is UNIX epoch in miliseconds
      */
     public function dailySalesHistogram(){
        $query = "SELECT takingsid, UNIX_TIMESTAMP(`date`)*1000 as sales_date,
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
        $sales_arr["start"] = $this->startdate;
        $sales_arr["end"] = $this->enddate;
        $sales_arr["shopid"] =$this->shopID?$this->shopID:'';
        $sales_arr["average"] = 0;
        $sales_arr["count"] = 0;
        $sales_arr["data"]=array();
        $sales_arr["last"]=array();
        $sales_arr["list"]=array();

        $sum =0; // sum of daily sales as we loop over rows

        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
                
                $sum = $sum + $row['total_after_expenses_and_donations'];
                array_push($sales_arr["data"], array($row['sales_date']
                    , $row['total_after_expenses_and_donations']));

                // Unix timestamp is timezone independent
                // These timestamps are for midnight on each day
                // If timezone is not taken into accoun then during BST dates will 
                // be 1 day earlier: (23:00 instead of 24:00)
                $dt = (new DateTime())->setTimestamp($row["sales_date"]/1000);
                $dt->setTimezone(new DateTimeZone('Europe/London'));
                array_push($sales_arr["list"], array($row["takingsid"],$dt->format('Y-m-d')
                    ,$row['total_after_expenses_and_donations']));
            }
            $sales_arr["count"] = $num;
            $sales_arr["average"] = round($sum / $sales_arr["count"],2);

            $sales_arr["last"] = end($sales_arr["list"]);
            
        }       

        return $sales_arr;
    }
}