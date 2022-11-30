<?php

namespace Models;

use \PDO;

class TakingsSummary{
    // database conn 
    private $conn;

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    public function performanceSummary($shopid,$current_date){

        // MySQL stored procedure
        $query = "CALL sales_table(:shopid,:date)";

        $stmt = $this->conn->prepare( $query );

        // bind id of product to be updated
        $stmt->bindParam(":shopid", $shopid, PDO::PARAM_INT);
        $stmt->bindParam(":date", $current_date, PDO::PARAM_STR);

        // execute query
        $stmt->execute();
        $num = $stmt->rowCount();

        $summary_arr=array();

        // check if more than 0 record found
        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
            
                $summary_item=array(
                    "index" => $index,
                    "period" => $Period,
                    "type" => $Type,
                    "start_date" => $start_date,
                    "end_date" => $end_date,
                    "count" => $count,
                    "number_of_items_sold" => $number_of_items_sold,
                    "net_sales" => $sales_after_expenses_and_donations,
                    "clothing" => $clothing,
                    "brica" => $brica,
                    "books" => $books,
                    "linens" => $linens,
                    "customers_num_total" => $customers_num_total,
                    "cash_to_bank" => $cash_to_bank,
                    "credit_cards" => $credit_cards,
                    "expenses" => $expenses,
                    "sales_total" => $sales,                    
                    "shopid" => $shopid,
                    "clothing_num" => $clothing_num,
                    "brica_num" => $brica_num,
                    "books_num" => $books_num,
                    "linens_num" => $linens_num,
                    "donations_num" => $donations_num,
                    "other_num" => $other_num,
                    "rag_num" => $rag_num,
                    "other" => $other,
                    "rag" => $rag,
                    "donations" => $donations,

                );

                // create un-keyed list
                array_push ($summary_arr, $summary_item);
            }
        }

        return $summary_arr;
    }

    public function salesChart($shopid,$current_date, $number_of_days = 10){

        // MySQL stored procedure
        $query = "CALL sales_chart(:shopid,:date,:number)";

        $stmt = $this->conn->prepare( $query );

        // bind id of product to be updated
        $stmt->bindParam(":shopid", $shopid, PDO::PARAM_INT);
        $stmt->bindParam(":date", $current_date, PDO::PARAM_STR);
        $stmt->bindParam(":number", $number_of_days, PDO::PARAM_INT);

        // execute query
        $stmt->execute();
        $num = $stmt->rowCount();

        $chart_data=array();
        $chart_data["current_date"] = $current_date;
        $chart_data["shopid"] = $shopid;
        $chart_data['dates'] = array();
        $chart_data['sales'] = array();        
        $chart_data['avg'] = array();
        $chart_data['avg30'] = array();
        $chart_data['avg365'] = array();
        $chart_data['avgAll'] = array();

        // check if more than 0 record found
        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);

                // create un-keyed lists
                array_push ($chart_data['dates'], $date);
                array_push ($chart_data['sales'], array($sales_date, $net_sales));                
                array_push ($chart_data['avg'], array($sales_date, $AvgSales));
                array_push ($chart_data['avg30'], array($sales_date, $AvgSalesLast30Days));
                array_push ($chart_data['avg365'], array($sales_date, $AvgSalesLast365Days));
                array_push ($chart_data['avgAll'], array($sales_date, $AvgSalesEver));
            }
        }

        return $chart_data;
    }

    public function departmentChart($shopid,$current_date){

        // MySQL stored procedure
        $query = "CALL cumm_sales_by_dept(:shopid,:date)";

        $stmt = $this->conn->prepare( $query );

        // bind id of product to be updated
        $stmt->bindParam(":shopid", $shopid, PDO::PARAM_INT);
        $stmt->bindParam(":date", $current_date, PDO::PARAM_STR);

        // execute query
        $stmt->execute();
        $num = $stmt->rowCount();

        $department_sales=[
            'WTD' => [],
            'MTD' => [],
            'YTD' => [],
        ];

        // check if more than 0 record found
        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
            
                $data_row = array();
                $data_row["Clothing"] = $sum_clothing;
                $data_row["Bric-a-Brac"] = $sum_brica;
                $data_row["Books"] = $sum_books;
                $data_row["Linens"] = $sum_linens;
                $data_row["Other"] = $sum_other;
                $data_row["Ragging"] = $sum_rag;

                $item = array(
                    "clothing" => $sum_clothing,
                    "brica" => $sum_brica,
                    "books" => $sum_books,
                    "linens" => $sum_linens,
                    "other" => $sum_other,
                    "rag" => $sum_rag,
                );

                // add data
                $department_sales[$Type] = $item;
            }
        }

        return $department_sales;
    }

    public function salesByMonth($shopid,$current_date){

        // MySQL stored procedure
        $query = "SELECT t.shopid, MIN(`date`) as month_start, Month(`date`) as month, Year(`date`) as year
                        , COUNT(takingsid) as count
                        ,ROUND(SUM(clothing+brica+books+linens+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference),2)
                            as sum_total_after_expenses_and_donations
                        ,ROUND(AVG(clothing+brica+books+linens+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference),2) 
                            as avg_sales_after_expenses_and_donations
                        ,ROUND(AVG(clothing)+(AVG(other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference)/2),2) as avg_clothing
                        ,ROUND(AVG(brica)+AVG(other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference)/2,2) as avg_brica
                        ,ROUND(AVG(books),2) as avg_books, ROUND(AVG(linens),2) as avg_linens
                        FROM takings t
                        WHERE t.shopid = :shopid AND t.`date` >= :date
                        GROUP BY Month(`date`), YEAR(`date`)
                        HAVING COUNT(takingsid) > 17
                        ORDER BY `date`";

        $stmt = $this->conn->prepare( $query );

        // bind id of product to be updated
        $stmt->bindParam(":shopid", $shopid, PDO::PARAM_INT);
        $stmt->bindParam(":date", $current_date, PDO::PARAM_STR);

        // execute query
        $stmt->execute();
        $num = $stmt->rowCount();

        $monthly_sales=array();

        // check if more than 0 record found
        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
            
                $monthly_sales_item=array(
                    "shopid" => $shopid,
                    "month_start" => $month_start,
                    "month" => $month,
                    "year" => $year,
                    "count" => $count,
                    "sales" => $sum_total_after_expenses_and_donations,
                    "avg_sales" => $avg_sales_after_expenses_and_donations,
                    "avg_clothing" => $avg_clothing,
                    "avg_brica" => $avg_brica,
                    "avg_books" => $avg_books,
                    "avg_linens" => $avg_linens,
                );
        
                    // create nonindexed array
                    array_push ($monthly_sales, $monthly_sales_item);
            }
        }

        return $monthly_sales;
    }

    public function salesByQuarter($shopid,$current_date){

        // MySQL stored procedure
        $query = "SELECT t.shopid, MIN(`date`) as quarter_start, Quarter(`date`) as quarter, Year(`date`) as year
                    , COUNT(takingsid) as count
                    ,ROUND(SUM(clothing+brica+books+linens+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference),2)
                        as sum_total_after_expenses_and_donations
                    ,ROUND(AVG(clothing+brica+books+linens+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference),2) 
                        as avg_sales_after_expenses_and_donations
                    ,ROUND(AVG(clothing)+(AVG(other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference)/2),2) as avg_clothing
                    ,ROUND(AVG(brica)+AVG(other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference)/2,2) as avg_brica
                    ,ROUND(AVG(books),2) as avg_books, ROUND(AVG(linens),2) as avg_linens
                    FROM takings t
                    WHERE t.shopid = :shopid AND t.`date` >= :date
                    GROUP BY Quarter(`date`), YEAR(`date`)
                    ORDER BY `date`;
                    ";

        $stmt = $this->conn->prepare( $query );

        // bind id of product to be updated
        $stmt->bindParam(":shopid", $shopid, PDO::PARAM_INT);
        $stmt->bindParam(":date", $current_date, PDO::PARAM_STR);

        // execute query
        $stmt->execute();
        $num = $stmt->rowCount();

        $quarterly_sales=array();

        // check if more than 0 record found
        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
            
                $quarterly_sales_item=array(
                    "shopid" => $shopid,
                    "quarter_start" => $quarter_start,
                    "quarter" => $quarter,
                    "year" => $year,
                    "count" => $count,
                    "sales" => $sum_total_after_expenses_and_donations,
                    "avg_sales" => $avg_sales_after_expenses_and_donations,
                    "avg_clothing" => $avg_clothing,
                    "avg_brica" => $avg_brica,
                    "avg_books" => $avg_books,
                    "avg_linens" => $avg_linens,
                );
        
                    // create nonindexed array
                    array_push ($quarterly_sales, $quarterly_sales_item);
            }
        }

        return $quarterly_sales;
    }    
}