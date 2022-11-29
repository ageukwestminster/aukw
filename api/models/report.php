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
                array_push($sales_arr["data"], array($row['sales_date'], $row['total_after_expenses_and_donations']));
                array_push($sales_arr["list"], array($row["takingsid"],gmdate("Y-m-d", $row["sales_date"]/1000),$row['total_after_expenses_and_donations']));
            }
            $sales_arr["count"] = $num;
            $sales_arr["last"] = array(gmdate("Y-m-d", end($sales_arr["data"])[0]/1000), end($sales_arr["data"])[1]);
            $sales_arr["average"] = round($sum / $sales_arr["count"],2);
        }       

        return $sales_arr;
    }
}