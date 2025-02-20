<?php

namespace Models;

use \PDO;

/**Used to convert between Unix timestamp and London dates */
use DateTime;
use DateTimeZone;

/**
 * Build and execute the queries to retrive report data.
 * 
 * @category Model
 */
class Report{
    /**
     * Database connection
     * @var PDO|null
     */ 
    private $conn;
    /**
     * The start date of the report period, in ISO 8601 format.
     *
     * @var string
     */
    public string $startdate;
    /**
     * The end date of the report period, in ISO 8601 format.
     *
     * @var string
     */
    public string $enddate;
    /**
     * The id of the shop
     * @var int
     */
    public $shopID;

    /**
     * Instantiate object
     */
    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    /**
      * Get data to build a histogram chart from net daily sales. HighCharts date format
      * is UNIX epoch in miliseconds.
      */
    public function dailySalesHistogram(){
        // The addition of the 61.2million miliseconds is to force the date into the correct day, even during BST
        // The exact number (61.2m) does not really matter as the chart only shows data to the nearest day
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
      *    set will be incorrect. i.e. the rolling average numbers at the start of the
      *    series are incorrect until you have had enough data points to have the correct denominator
      * @return array
      */
    public function dailySalesMovingAverage() : array{
        // The addition of the 61.2million miliseconds is to force the date into the correct day, even during BST
        // The exact number (61.2m) does not really matter as the chart only shows data to the nearest day
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

        $sales_arr=array();
        $sales_arr["shopid"] = $this->shopID?$this->shopID:'';
        $sales_arr["start"] = $this->startdate;
        $sales_arr["dates"]=array();
        $sales_arr["avg20"]=array();
        $sales_arr["avgQuarter"]=array();

        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);

                array_push($sales_arr["dates"], $row['date']);
                array_push($sales_arr["avg20"], array($row["sales_timestamp"],$row['twenty_day_avg']));
                array_push($sales_arr["avgQuarter"], array($row["sales_timestamp"],$row['quarter_avg']));
            }
           
        }       

        return $sales_arr;
    }

    /**
     * 
     */
    public function salesByDepartment() : array{

        $query = "SELECT SUM(clothing) as clothing, SUM(brica) as brica, SUM(books) as books
	                    , SUM(linens) as linens, SUM(other) as other
                        , SUM(clothing+brica+books+linens+other) as total
                    FROM takings t
                    WHERE t.date >= :start AND t.date <= :end" .
                    ($this->shopID ? ' AND t.shopID = :shopID ' : ' ');
        
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
        $dept_list=array();

        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
                
                $dept_list=array(
                    "clothing" => $clothing,
                    "brica" => $brica,
                    "books" => $books,
                    "linens" => $linens,
                    "other" => $other,
                    "total" => $total,
                );


            }


        }       

        return array_merge($sales_arr, $dept_list);
    }
}