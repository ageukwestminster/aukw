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
      * The addition of the 61.2million miliseconds is to force the date into the correct day, even during BST
      */
    public function dailySalesHistogram(){
        $query = "SELECT takingsid, UNIX_TIMESTAMP(`date`)*1000 +61200000 as sales_date,
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
        $sales_arr["count"] = $num;
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
                // If timezone is not taken into account then during BST dates will 
                // be 1 day earlier: (23:00 instead of 24:00)
                $dt = (new DateTime())->setTimestamp($row["sales_date"]/1000);
                $dt->setTimezone(new DateTimeZone('Europe/London'));
                array_push($sales_arr["list"], array($row["takingsid"],$dt->format('Y-m-d')
                    ,$row['total_after_expenses_and_donations']));
            }

            $sales_arr["average"] = round($sum / $sales_arr["count"],2);

            $sales_arr["last"] = end($sales_arr["list"]);
            
        }       

        return $sales_arr;
    }

    /**
      * Get data to build a chart of moving average sales values.
      *
      * Note:
      * 1) HighCharts date format is UNIX epoch in miliseconds
      * 2) MariaDB windows frame method means moving average of start of data
      *    set will be incorrect. So we take an extra year of data and then discard
      * 3) The addition of the 61.2million miliseconds is to force the date into the correct day, even during BST
      */
    public function dailySalesMovingAverage(){
        $query = "SELECT `date`, UNIX_TIMESTAMP(`date`)*1000 + 61200000 as sales_timestamp
                    ,clothing+brica+books+linens+other-operating_expenses-volunteer_expenses-other_adjustments+cash_difference as net_sales
                    ,AVG(clothing+brica+books+linens+other-operating_expenses-volunteer_expenses-other_adjustments+cash_difference)
                             OVER (order by date ASC ROWS 9 PRECEDING) as ten_day_avg 
                    ,AVG(clothing+brica+books+linens+other-operating_expenses-volunteer_expenses-other_adjustments+cash_difference)
                             OVER (order by date ASC ROWS 19 PRECEDING) as twenty_day_avg 
                    ,AVG(clothing+brica+books+linens+other-operating_expenses-volunteer_expenses-other_adjustments+cash_difference)
                             OVER (order by date ASC ROWS 74 PRECEDING) as quarter_avg 
                    ,AVG(clothing+brica+books+linens+other-operating_expenses-volunteer_expenses-other_adjustments+cash_difference)
                             OVER (order by date ASC ROWS 299 PRECEDING) as year_avg " .
                    /*    ,AVG(clothing) OVER (order by date ASC ROWS 9 PRECEDING) as clothing_ten_day_avg
                        ,AVG(brica) OVER (order by date ASC ROWS 9 PRECEDING) as brica_ten_day_avg
                        ,AVG(clothing) OVER (order by date ASC ROWS 19 PRECEDING) as clothing_twenty_day_avg
                        ,AVG(brica) OVER (order by date ASC ROWS 19 PRECEDING) as brica_twenty_day_avg
                        ,AVG(clothing) OVER (order by date ASC ROWS 74 PRECEDING) as clothing_quarter_avg
                        ,AVG(brica) OVER (order by date ASC ROWS 74 PRECEDING) as brica_quarter_avg
                        ,AVG(clothing) OVER (order by date ASC ROWS 299 PRECEDING) as clothing_year_avg
                        ,AVG(brica) OVER (order by date ASC ROWS 299 PRECEDING) as brica_year_avg*/
                    "FROM takings
                    WHERE `date` >= :start " .
                    ($this->shopID ? ' AND shopID = :shopID ' : ' ') ;
        
        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind id of product to be updated
        $stmt->bindParam(":start", $this->startdate);
        if ($this->shopID) {
            $shopID = filter_var($this->shopID, FILTER_SANITIZE_NUMBER_INT);
            $stmt->bindParam (":shopID", $shopID, PDO::PARAM_INT);
        }

        // execute query
        $stmt->execute();

        $num = $stmt->rowCount();

        // The cutoff date is at midnight, local time
        $cutoffdate = (new DateTime($this->startdate))->modify('+6 month');
        $cutoffdate->setTimezone(new DateTimeZone('Europe/London'));
        $cutoffdate->setTime(0,0); // midnight

        $sales_arr=array();
        $sales_arr["shopid"] = $this->shopID?$this->shopID:'';
        $sales_arr["start"] = $this->startdate;
        $sales_arr["dates"]=array();
        $sales_arr["net_sales"]=array();
        $sales_arr["avg10"]=array();
        $sales_arr["avg20"]=array();
        $sales_arr["avgQuarter"]=array();
        $sales_arr["avgYear"]=array();

        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);

                // Unix timestamp is timezone independent
                // These timestamps are for midnight on each day
                // If timezone is not taken into account then during BST dates will 
                // be 1 day earlier: (23:00 instead of 24:00)
                $dt = (new DateTime())->setTimestamp($row["sales_timestamp"]/1000);
                $dt->setTimezone(new DateTimeZone('Europe/London'));

                // check that the date is after the cutoff date.
                // We have the cutoff to ignore 1st years data because averages are wrong
                if($dt >= $cutoffdate) {
                    array_push($sales_arr["dates"], $row['date']);
                    //array_push($sales_arr["net_sales"], array($row['sales_timestamp'],$row['net_sales']));
                    //array_push($sales_arr["avg10"], array($row['sales_timestamp'],$row['ten_day_avg']));
                    array_push($sales_arr["avg20"], array($row["sales_timestamp"],$row['twenty_day_avg']));
                    array_push($sales_arr["avgQuarter"], array($row["sales_timestamp"],$row['quarter_avg']));
                    //array_push($sales_arr["avgYear"], array($row['sales_timestamp'],$row['year_avg']));
                }
            }
           
        }       

        return $sales_arr;
    }
}