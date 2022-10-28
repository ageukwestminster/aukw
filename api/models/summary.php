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
                    "number_of_items_sold" => $count,
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
                    "sales_num" => $sales_num,
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

    public function salesChart($shopid,$current_date){

        // MySQL stored procedure
        $query = "CALL sales_chart(:shopid,:date)";

        $stmt = $this->conn->prepare( $query );

        // bind id of product to be updated
        $stmt->bindParam(":shopid", $shopid, PDO::PARAM_INT);
        $stmt->bindParam(":date", $current_date, PDO::PARAM_STR);

        // execute query
        $stmt->execute();
        $num = $stmt->rowCount();

        $chart_data=array();

        // check if more than 0 record found
        if($num>0){
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
            
                $data_row=array(
                    "date" => $date,
                    "sales" => $net_sales,
                    "avg10" => $AvgSales,
                    "avg30" => $AvgSalesLast30Days,
                    "avg365" => $AvgSalesLast365Days,
                    "avgAll" => $AvgSalesEver,
                );

                // create un-keyed list
                array_push ($chart_data, $data_row);
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

}