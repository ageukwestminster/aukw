<?php

namespace Models;

use \PDO;

/**Used to convert between Unix timestamp and London dates */
use DateTime;
use DateTimeZone;
use \Models\RowItem;

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
   * Show results of a query for sales by department for the date range supplied.
   * Donations and rag are excluded.
   * @return array The required data
   */
    public function salesByDepartment() : array{
        try {
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
        } catch (\Exception $e) {
            http_response_code(400);  
            echo json_encode(
              array(
                "message" => "Unable to generate sales by department report.",
                "extra" => $e->getMessage()
              )
            );
            exit(1);
        }
    }

  /**
   * Show results of a query for average transaction size and average value per transaction
   * for the date range supplied, and for the period 12 months before that date range.
   * @return array The required data
   */
    public function avgDailyTransactionSize() : array{        
        try {
            $return = array();
            $return['title'] = 'Average Transaction Number & Size';
            $return['shopid'] = $this->shopID??null;
            $return['range'] = array();
            $return['range']['currentPeriodStart'] = $this->startdate;
            $return['range']['currentPeriodEnd'] = $this->enddate;

            $currentPeriod = $this->getAvgDailyTransactionSize($this->startdate, $this->enddate);

            // Do Previous year's values ... this means perform the query again, this time
            // for a period that is 12 months before the current period
            $prevStartDate = (new DateTime($this->startdate))->modify('-1 year')->format('Y-m-d');
            $prevEndDate = (new DateTime($this->enddate))->modify('-1 year')->format('Y-m-d');
            $return['range']['previousPeriodStart'] = $prevStartDate;
            $return['range']['previousPeriodEnd'] = $prevEndDate;
            $previousPeriod = $this->getAvgDailyTransactionSize($prevStartDate, $prevEndDate);

            $rowItem = new RowItem;
            $rowItem->displayName = "Average number of transactions per day";
            $rowItem->currentValue = $currentPeriod['avg_daily_transactions'];
            $rowItem->previousValue = $previousPeriod['avg_daily_transactions'];
            $return['avg_daily_transactions'] = $rowItem;

            $rowItem = new RowItem;
            $rowItem->displayName = "Average value per transaction";
            $rowItem->currentValue = $currentPeriod['sales_per_txn'];
            $rowItem->previousValue = $previousPeriod['sales_per_txn'];
            $return['sales_per_txn'] = $rowItem;

            $rowItem = new RowItem;
            $rowItem->displayName = "Number of trading days in the period";
            $rowItem->currentValue = $currentPeriod['trading_days_in_period'];
            $rowItem->previousValue = $previousPeriod['trading_days_in_period'];
            $return['trading_days_in_period'] = $rowItem;

            $rowItem = new RowItem;
            $rowItem->displayName = "Computed total of Sales";
            $rowItem->currentValue = round($currentPeriod['trading_days_in_period']*
                $currentPeriod['sales_per_txn']*$currentPeriod['avg_daily_transactions'],2);
            $rowItem->previousValue = round($previousPeriod['trading_days_in_period']*
                $previousPeriod['sales_per_txn']*$previousPeriod['avg_daily_transactions'],2);
            $return['computed_total'] = $rowItem;

            $rowItem = new RowItem;
            $rowItem->displayName = "Actual total of Sales";
            $rowItem->currentValue = $currentPeriod['total'];
            $rowItem->previousValue = $previousPeriod['total'];
            $return['actual_total'] = $rowItem;

            return $return;
        } catch (\Exception $e) {
            http_response_code(400);  
            echo json_encode(
              array(
                "message" => "Unable to generate average daily transaction size report.",
                "extra" => $e->getMessage()
              )
            );
            exit(1);
        }
    }

    /**
     * Private function to perform the actual MariaDB query for average transaction size 
     * and average value per transaction for the date range supplied.
     * @param string $startdate The beginning date of the accounting period
     * @param string $enddate The end date of the accounting period
     * @return array 
     */
    private function getAvgDailyTransactionSize($start, $end) {
        
        $query = "SELECT  count(*) as trading_days_in_period
                        , ROUND(AVG(customers_num_total),1) as avg_daily_transactions
                        , ROUND(SUM(clothing+brica+books+linens+other)/sum(customers_num_total),2) as sales_per_txn
                        , SUM(clothing+brica+books+linens+other) as total
                        FROM takings t
                        WHERE t.date >= :start AND t.date <= :end" .
                        ($this->shopID ? ' AND t.shopID = :shopID ' : ' ');

        $stmt = $this->conn->prepare( $query );
        // bind id of product to be updated
        $stmt->bindParam(":start", $start);
        $stmt->bindParam(":end", $end);
        if ($this->shopID) {
            $shopID = filter_var($this->shopID, FILTER_SANITIZE_NUMBER_INT);
            $stmt->bindParam (":shopID", $shopID, PDO::PARAM_INT);
        }

        // execute query
        $stmt->execute();
        $num = $stmt->rowCount();

        // check if more than 0 records found
        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
            
                return array(

                    "trading_days_in_period" => $trading_days_in_period,
                    "avg_daily_transactions" => $avg_daily_transactions,
                    "sales_per_txn" => $sales_per_txn,
                    "total" => $total,
                );
        
                    // create nonindexed array
                    array_push ($quaterly_data, $avg_weekly_sales);
            }
        }
    }

    /**
   * Show results of a query for average in-store income per shop per week
   * for the date range supplied, and for the period 12 months before that date range.
   * @return array The required data
   */
    public function avgWeeklySales() : array{        
        try {
            $return = array();
            $return['title'] = 'Average In-Store Income Per Week';
            $return['shopid'] = $this->shopID??null;
            $return['range'] = array();
            $return['range']['currentPeriodStart'] = $this->startdate;
            $return['range']['currentPeriodEnd'] = $this->enddate;

            $currentPeriod = $this->getAvgWeeklyInstoreIncome($this->startdate, $this->enddate);

            // Do Previous year's values ... this means perform the query again, this time
            // for a period that is 12 months before the current period
            $prevStartDate = (new DateTime($this->startdate))->modify('-1 year')->format('Y-m-d');
            $prevEndDate = (new DateTime($this->enddate))->modify('-1 year')->format('Y-m-d');
            $return['range']['previousPeriodStart'] = $prevStartDate;
            $return['range']['previousPeriodEnd'] = $prevEndDate;
            $previousPeriod = $this->getAvgWeeklyInstoreIncome($prevStartDate, $prevEndDate);

            $rowItem = new RowItem;
            $rowItem->displayName = "Average instore sales per week";
            $rowItem->currentValue = $currentPeriod['avg_weekly_sales'];
            $rowItem->previousValue = $previousPeriod['avg_weekly_sales'];
            $return['avg_weekly_sales'] = $rowItem;

            $rowItem = new RowItem;
            $rowItem->displayName = "Number of weeks in the period";
            $rowItem->currentValue = $currentPeriod['week_count'];
            $rowItem->previousValue = $previousPeriod['week_count'];
            $return['week_count'] = $rowItem;

            $rowItem = new RowItem;
            $rowItem->displayName = "Number of trading days in the period";
            $rowItem->currentValue = $currentPeriod['trading_days_in_period'];
            $rowItem->previousValue = $previousPeriod['trading_days_in_period'];
            $return['trading_days_in_period'] = $rowItem;

            $rowItem = new RowItem;
            $rowItem->displayName = "Computed total of Sales";
            $rowItem->currentValue = round($currentPeriod['week_count']*
                $currentPeriod['avg_weekly_sales'],2);
            $rowItem->previousValue = round($previousPeriod['week_count']*
                $previousPeriod['avg_weekly_sales'],2);
            $return['computed_total'] = $rowItem;

            $rowItem = new RowItem;
            $rowItem->displayName = "Actual total of Sales";
            $rowItem->currentValue = $currentPeriod['total'];
            $rowItem->previousValue = $previousPeriod['total'];
            $return['actual_total'] = $rowItem;

            return $return;
        } catch (\Throwable $e) {
            http_response_code(400);  
            echo json_encode(
            array(
                "message" => "Unable to generate average daily transaction size report.",
                "extra" => $e->getMessage()
            )
            );
            exit(1);
        }
    }

    /**
     * Private function to perform the actual MariaDB query for average in-store income 
     * per shop per week for the date range supplied.
     * @param string $startdate The beginning date of the accounting period
     * @param string $enddate The end date of the accounting period
     * @return array 
     */
    private function getAvgWeeklyInstoreIncome($start, $end) {
        
        $query = "SELECT SUM(clothing+brica+books+linens+other) as `instore_sales`
                , COUNT(*) as number_of_trading_days
                , WEEK(Min(date)) as first_week, WEEK(Max(date)) as last_week
                , ROUND(DATEDIFF(Max(date), Min(date))/7, 2) AS week_count
                , ROUND(SUM(clothing+brica+books+linens+other)*7/DATEDIFF(Max(date), Min(date)),2) as avg_weekly_sales
                FROM takings t
                WHERE t.date >= :start AND t.date <= :end" .
                ($this->shopID ? ' AND t.shopID = :shopID ' : ' ');

        $stmt = $this->conn->prepare( $query );
        // bind id of product to be updated
        $stmt->bindParam(":start", $start);
        $stmt->bindParam(":end", $end);
        if ($this->shopID) {
            $shopID = filter_var($this->shopID, FILTER_SANITIZE_NUMBER_INT);
            $stmt->bindParam (":shopID", $shopID, PDO::PARAM_INT);
        }

        // execute query
        $stmt->execute();
        $num = $stmt->rowCount();

        // check if more than 0 records found
        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
            
                return array(

                    "total" => $instore_sales,
                    "trading_days_in_period" => $number_of_trading_days,
                    "first_week" => $first_week,
                    "last_week" => $last_week,
                    "week_count" => $week_count,
                    "avg_weekly_sales" => $avg_weekly_sales,
                );
        
                    // create nonindexed array
                    array_push ($quaterly_data, $avg_weekly_sales);
            }
        }
    }

        /**
      * Get data to build a chart of moving average cash to credit cards.
      *
      * Note:
      * 1) HighCharts date format is UNIX epoch in miliseconds
      * 2) MariaDB windows frame method means moving average of start of data
      *    set will be incorrect. i.e. the rolling average numbers at the start of the
      *    series are incorrect until you have had enough data points to have the correct denominator
      * @return array
      */
      public function cashRatioMovingAverage() : array{
        // The addition of the 61.2million miliseconds is to force the date into the correct day, even during BST
        // The exact number (61.2m) does not really matter as the chart only shows data to the nearest day
        $query = "SELECT `date`, UNIX_TIMESTAMP(`date`)*1000 + 61200000 as sales_timestamp
                , cash_to_bank, credit_cards
                , IF(credit_cards = 0,100,ROUND(100*IF(cash_to_bank=0,0,cash_to_bank/(cash_to_bank+credit_cards)),2)) as ratio".
                //,AVG(IF(credit_cards = 0,100,100*IF(cash_to_bank=0,0,cash_to_bank/(cash_to_bank+credit_cards))))
                //        OVER (order by date ASC ROWS 9 PRECEDING) as ten_day_avg 
                ",ROUND(AVG(IF(credit_cards = 0,100,100*IF(cash_to_bank=0,0,cash_to_bank/(cash_to_bank+credit_cards))))
                        OVER (order by date ASC ROWS 19 PRECEDING),2) as twenty_day_avg 
                ,ROUND(AVG(IF(credit_cards = 0,100,100*IF(cash_to_bank=0,0,cash_to_bank/(cash_to_bank+credit_cards))))
                        OVER (order by date ASC ROWS 74 PRECEDING),2) as quarter_avg " .
                //,AVG(IF(credit_cards = 0,100,100*IF(cash_to_bank=0,0,cash_to_bank/(cash_to_bank+credit_cards))))
                //        OVER (order by date ASC ROWS 299 PRECEDING) as year_avg  " .
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
        $sales_arr["ratio"]=array();

        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);

                array_push($sales_arr["dates"], $row['date']);
                array_push($sales_arr["ratio"], array($row["sales_timestamp"],$row['ratio']));
                array_push($sales_arr["avg20"], array($row["sales_timestamp"],$row['twenty_day_avg']));
                array_push($sales_arr["avgQuarter"], array($row["sales_timestamp"],$row['quarter_avg']));

            }
           
        }       

        return $sales_arr;
    }

}