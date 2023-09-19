<?php

namespace Models;

use \PDO;

/**
 * [Description Takings]
 * 
 * @category Model
 */
class Takings{
    // database conn 
    private $conn;
    // table name
    private $table_name = "takings";

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    // object properties
    public $id;
    public $date;
    public $shopid;
    public $clothing_num;
    public $brica_num;
    public $books_num;
    public $linens_num;
    public $donations_num;
    public $other_num;
    public $rag_num;
    public $clothing;
    public $brica;
    public $books;
    public $linens;
    public $donations;
    public $other;
    public $rag;
    public $customers_num_total;
    public $cash_to_bank;
    public $credit_cards;
    public $operating_expenses;
    public $volunteer_expenses;
    public $other_adjustments;
    public $cash_to_charity;
    public $cash_difference;
    public $comments;
    public $rags_paid_in_cash;
    public $timestamp;
    public $quickbooks;

    // Show takings data  for a given shop, between start and end dates
    public function summary($shopid, $startdate, $enddate){
        $query = "SELECT
                    takingsid, `date`, t.shopid, s.`name` as shopname, 
                    (clothing_num+brica_num+books_num+linens_num+donations_num+other_num+rag_num) as number_of_items_sold,
                    `customers_num_total`,
                    (clothing+brica+books+linens+donations+other) as sales_total,
                    rag, (clothing+brica+books+linens+donations+other+rag) as sales_total_inc_rag,
                    cash_to_bank, `credit_cards`, cash_difference,
                    (operating_expenses+volunteer_expenses+other_adjustments-cash_difference) as expenses,
                    (clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference) as total_after_expenses,
                    (clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) as total_after_expenses_and_donations,
                    `comments`, t.`quickbooks`
                    FROM
                    " . $this->table_name . " t
                    LEFT JOIN shop s ON t.shopid = s.id
                    WHERE t.shopid = :shopid AND t.`date` >= :start AND t.`date`<= :end
                    ORDER BY t.`date` DESC
                    ";
        
        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind id of product to be updated
        $stmt->bindParam(":shopid", $shopid, PDO::PARAM_INT);
        $stmt->bindParam(":start", $startdate);
        $stmt->bindParam(":end", $enddate);

        // execute query
        $stmt->execute();

        $num = $stmt->rowCount();

        $item_arr=array();

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
            
                $takings_item=array(
                    "id" => $takingsid,
                    "date" => $date,
                    "shopid" => $shopid,
                    "shopname" => html_entity_decode($shopname),
                    "number_of_items_sold" => $number_of_items_sold,
                    "customers_num_total" => $customers_num_total,
                    "sales_total" => $sales_total,
                    "rag" => $rag,
                    "sales_total_inc_rag" => $sales_total_inc_rag,
                    "expenses" => $expenses,
                    "cash_difference" => $cash_difference,
                    "total_after_expenses" => $total_after_expenses,
                    "daily_net_sales" => $total_after_expenses_and_donations,
                    "comments" => html_entity_decode($comments ?? ''),
                    "quickbooks" => $quickbooks,
                );

                $item_arr[] = $takings_item;
            }
        }

        return $item_arr;
    }

       

    // Show takings data for the last 90 days for a given shop
    public function read_by_shop($shopid){
        $query = "SELECT
                    takingsid as `id`, `date`, shopid, clothing_num, brica_num,
                    books_num, linens_num, donations_num, other_num, rag_num, clothing,
                    brica, books, linens, donations, other, rag, `customers_num_total`,
                    cash_to_bank, `credit_cards`,`operating_expenses`,`volunteer_expenses`,
                    `other_adjustments`, `cash_to_charity`, `cash_difference`,`comments`,
                    `rags_paid_in_cash`,`timestamp`,quickbooks
                    FROM
                    " . $this->table_name . "
                    WHERE shopid = :shopid AND `date` > DATE_sub(NOW(), INTERVAL 90 DAY)
                    ORDER BY `date` DESC
                    ";
        
        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind id of product to be updated
        $stmt->bindParam(":shopid", $shopid, PDO::PARAM_INT);

        // execute query
        $stmt->execute();

        $num = $stmt->rowCount();

        $item_arr=array();

        // check if more than 0 record found
        if($num>0){
            // retrieve our table contents
            // fetch() is faster than fetchAll()
            // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){            
                $item = Takings::pass_row_data($row);
                $item_arr[] = $item;
            }
        }

        return $item_arr;
    }

    // Set $quickbooks = 0 to see all takings that are not in QB, set to 1 to see all takings that are in QB
    // No other values are valid
    public function read_by_quickbooks_status($quickbooks){
        $query = "SELECT
                    takingsid as `id`, `date`, shopid, clothing_num, brica_num,
                    books_num, linens_num, donations_num, other_num, rag_num, clothing,
                    brica, books, linens, donations, other, rag, `customers_num_total`,
                    cash_to_bank, `credit_cards`,`operating_expenses`,`volunteer_expenses`,
                    `other_adjustments`, `cash_to_charity`, `cash_difference`,`comments`,
                    `rags_paid_in_cash`,`timestamp`,quickbooks
                    FROM
                    " . $this->table_name . "
                    WHERE quickbooks = :quickbooks
                    ";
        
        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind id of product to be updated
        $stmt->bindParam(":quickbooks", $quickbooks, PDO::PARAM_INT);

        // execute query
        $stmt->execute();

        $num = $stmt->rowCount();

        $item_arr=array();

        // check if more than 0 record found
        if($num>0){
            // retrieve our table contents
            // fetch() is faster than fetchAll()
            // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $item = Takings::pass_row_data($row);
                $item_arr[] = $item;
            }
        }

        return $item_arr;
    }

    public function read_most_recent($shopid){
        $query = "SELECT
                    takingsid as `id`, `date`, shopid, clothing_num, brica_num,
                    books_num, linens_num, donations_num, other_num, rag_num, clothing,
                    brica, books, linens, donations, other, rag, `customers_num_total`,
                    cash_to_bank, `credit_cards`,`operating_expenses`,`volunteer_expenses`,
                    `other_adjustments`, `cash_to_charity`, `cash_difference`,`comments`,
                    `rags_paid_in_cash`,`timestamp`,quickbooks
                    FROM
                    " . $this->table_name . "
                    WHERE shopid = :shopid 
                    ORDER BY date DESC LIMIT 0,1
                    ";
        
        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind id of product to be updated
        $stmt->bindParam(":shopid", $shopid, PDO::PARAM_INT);

        // execute query
        $stmt->execute();

        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row){
            return Takings::pass_row_data($row);
        }
    }    

    function readOne(){
        $query = "SELECT
                    takingsid, `date`, shopid, clothing_num, brica_num,
                    books_num, linens_num, donations_num, other_num, rag_num, clothing,
                    brica, books, linens, donations, other, rag, `customers_num_total`,
                    cash_to_bank, `credit_cards`,`operating_expenses`,`volunteer_expenses`,
                    `other_adjustments`, `cash_to_charity`, `cash_difference`,`comments`,
                    `rags_paid_in_cash`,`timestamp`,quickbooks
                    FROM
                    " . $this->table_name . "
                    WHERE takingsid = ?
                    LIMIT 0,1";
        
        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind id of product to be updated
        $stmt->bindParam(1, $this->id);

        // execute query
        $stmt->execute();

        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->date = $row['date'];
            $this->shopid = $row['shopid'];
            $this->clothing_num = $row['clothing_num'];
            $this->brica_num = $row['brica_num'];
            $this->books_num = $row['books_num'];
            $this->linens_num = $row['linens_num'];
            $this->donations_num = $row['donations_num'];
            $this->other_num = $row['other_num'];
            $this->rag_num = $row['rag_num'];
            $this->clothing = $row['clothing'];
            $this->brica = $row['brica'];
            $this->books = $row['books'];
            $this->linens = $row['linens'];
            $this->donations = $row['donations'];
            $this->other = $row['other'];
            $this->rag = $row['rag'];
            $this->customers_num_total = $row['customers_num_total'];
            $this->cash_to_bank = $row['cash_to_bank'];
            $this->credit_cards = $row['credit_cards'];
            $this->operating_expenses = $row['operating_expenses'];
            $this->volunteer_expenses = $row['volunteer_expenses'];
            $this->other_adjustments = $row['other_adjustments'];
            $this->cash_to_charity = $row['cash_to_charity'];
            $this->cash_difference = $row['cash_difference'];
            $this->comments = $row['comments'];
            $this->rags_paid_in_cash = $row['rags_paid_in_cash'];
            $this->timestamp = $row['timestamp'];
            $this->quickbooks = $row['quickbooks'];

            return array(
                "id" => $this->id,
                "date" => $this->date,
                "shopid" => $this->shopid,
                "clothing_num" => $this->clothing_num,
                "brica_num" => $this->brica_num,
                "books_num" => $this->books_num,
                "linens_num" => $this->linens_num,
                "donations_num" => $this->donations_num,
                "other_num" => $this->other_num,
                "rag_num" => $this->rag_num,
                "clothing" => $this->clothing,
                "brica" => $this->brica,
                "books" => $this->books,
                "linens" => $this->linens,
                "donations" => $this->donations,
                "other" => $this->other,
                "rag" => $this->rag,
                "customers_num_total" => $this->customers_num_total,
                "cash_to_bank" => $this->cash_to_bank,
                "credit_cards" => $this->credit_cards,
                "operating_expenses" => $this->operating_expenses,
                "volunteer_expenses" => $this->volunteer_expenses,
                "other_adjustments" => $this->other_adjustments,
                "cash_to_charity" => $this->cash_to_charity,
                "cash_difference" => $this->cash_difference,
                "comments" => $this->comments,
                "rags_paid_in_cash" => $this->rags_paid_in_cash,
                "timestamp" => $this->timestamp,
                "quickbooks" => $this->quickbooks 
            );
        }
    }

    function create(){
        $query = "INSERT INTO
                    " . $this->table_name . "
                    SET 
                    date=:date,
                    shopid=:shopid,
                    clothing_num=:clothing_num, 
                    clothing=:clothing,
                    brica_num=:brica_num, 
                    books_num=:books_num, 
                    linens_num=:linens_num, 
                    donations_num=:donations_num, 
                    other_num=:other_num, 
                    rag_num=:rag_num, 
                    brica=:brica, 
                    books=:books, 
                    linens=:linens, 
                    donations=:donations, 
                    other=:other, 
                    rag=:rag, 
                    customers_num_total=:customers_num_total,
                    cash_to_bank=:cash_to_bank, 
                    credit_cards=:credit_cards,
                    operating_expenses=:operating_expenses, 
                    volunteer_expenses=:volunteer_expenses, 
                    cash_difference=:cash_difference,
                    comments=:comments,
                    quickbooks=0,
                    timestamp=NULL;";

        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->date=htmlspecialchars(strip_tags($this->date));
        $this->shopid=htmlspecialchars(strip_tags($this->shopid));
        $this->clothing_num=htmlspecialchars(strip_tags($this->clothing_num));
        $this->brica_num=htmlspecialchars(strip_tags($this->brica_num));
        $this->books_num=htmlspecialchars(strip_tags($this->books_num));
        $this->linens_num=htmlspecialchars(strip_tags($this->linens_num));
        $this->donations_num=htmlspecialchars(strip_tags($this->donations_num));
        $this->other_num=htmlspecialchars(strip_tags($this->other_num));
        $this->rag_num=htmlspecialchars(strip_tags($this->rag_num));
        $this->clothing=htmlspecialchars(strip_tags($this->clothing));
        $this->brica=htmlspecialchars(strip_tags($this->brica));
        $this->books=htmlspecialchars(strip_tags($this->books));
        $this->linens=htmlspecialchars(strip_tags($this->linens));
        $this->donations=htmlspecialchars(strip_tags($this->donations));
        $this->other=htmlspecialchars(strip_tags($this->other));
        $this->rag=htmlspecialchars(strip_tags($this->rag));
        $this->customers_num_total=htmlspecialchars(strip_tags($this->customers_num_total));
        $this->cash_to_bank=htmlspecialchars(strip_tags($this->cash_to_bank));
        $this->credit_cards=htmlspecialchars(strip_tags($this->credit_cards));
        $this->operating_expenses=htmlspecialchars(strip_tags($this->operating_expenses));
        $this->volunteer_expenses=htmlspecialchars(strip_tags($this->volunteer_expenses));
        $this->cash_difference=htmlspecialchars(strip_tags($this->cash_difference));
        $this->comments=htmlspecialchars(strip_tags($this->comments));

        // bind values
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":shopid", $this->shopid);
        $stmt->bindParam(":clothing_num", $this->clothing_num);
        $stmt->bindParam(":brica_num", $this->brica_num);
        $stmt->bindParam(":books_num", $this->books_num);
        $stmt->bindParam(":linens_num", $this->linens_num);
        $stmt->bindParam(":donations_num", $this->donations_num);
        $stmt->bindParam(":other_num", $this->other_num);
        $stmt->bindParam(":rag_num", $this->rag_num);
        $stmt->bindParam(":clothing", $this->clothing);
        $stmt->bindParam(":brica", $this->brica);
        $stmt->bindParam(":books", $this->books);
        $stmt->bindParam(":linens", $this->linens);
        $stmt->bindParam(":donations", $this->donations);
        $stmt->bindParam(":other", $this->other);
        $stmt->bindParam(":rag", $this->rag);
        $stmt->bindParam(":customers_num_total", $this->customers_num_total);
        $stmt->bindParam(":cash_to_bank", $this->cash_to_bank);
        $stmt->bindParam(":credit_cards", $this->credit_cards);
        $stmt->bindParam(":operating_expenses", $this->operating_expenses);
        $stmt->bindParam(":volunteer_expenses", $this->volunteer_expenses);
        $stmt->bindParam(":cash_difference", $this->cash_difference);
        if($this->comments == '') {            
            $stmt->bindParam(":comments", $this->null, PDO::PARAM_STR);
        }
        else {
            $stmt->bindParam(":comments", $this->comments);
        }

        // execute query
        try {
            if($stmt->execute()){
                $this->id = $this->conn->lastInsertId();
                if($this->id) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        catch (\Exception $e) {
            return false;
        }
        
        return false;
    }

    function update(){
        $query = "UPDATE
                    " . $this->table_name . "
                    SET 
                    date=:date,
                    shopid=:shopid,
                    clothing_num=:clothing_num, 
                    clothing=:clothing,
                    brica_num=:brica_num, 
                    books_num=:books_num, 
                    linens_num=:linens_num, 
                    donations_num=:donations_num, 
                    other_num=:other_num, 
                    rag_num=:rag_num, 
                    brica=:brica, 
                    books=:books, 
                    linens=:linens, 
                    donations=:donations, 
                    other=:other, 
                    rag=:rag, 
                    customers_num_total=:customers_num_total,
                    cash_to_bank=:cash_to_bank, 
                    credit_cards=:credit_cards,
                    operating_expenses=:operating_expenses, 
                    volunteer_expenses=:volunteer_expenses, 
                    cash_difference=:cash_difference,
                    comments=:comments,
                    rags_paid_in_cash=:rags_paid_in_cash,
                    quickbooks=:quickbooks,
                    timestamp=NULL 
                WHERE 
                    takingsid=:id;";

        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->date=htmlspecialchars(strip_tags($this->date));
        $this->shopid=htmlspecialchars(strip_tags($this->shopid));
        $this->clothing_num=htmlspecialchars(strip_tags($this->clothing_num));
        $this->brica_num=htmlspecialchars(strip_tags($this->brica_num));
        $this->books_num=htmlspecialchars(strip_tags($this->books_num));
        $this->linens_num=htmlspecialchars(strip_tags($this->linens_num));
        $this->donations_num=htmlspecialchars(strip_tags($this->donations_num));
        $this->other_num=htmlspecialchars(strip_tags($this->other_num));
        $this->rag_num=htmlspecialchars(strip_tags($this->rag_num));
        $this->clothing=htmlspecialchars(strip_tags($this->clothing));
        $this->brica=htmlspecialchars(strip_tags($this->brica));
        $this->books=htmlspecialchars(strip_tags($this->books));
        $this->linens=htmlspecialchars(strip_tags($this->linens));
        $this->donations=htmlspecialchars(strip_tags($this->donations));
        $this->other=htmlspecialchars(strip_tags($this->other));
        $this->rag=htmlspecialchars(strip_tags($this->rag));
        $this->customers_num_total=htmlspecialchars(strip_tags($this->customers_num_total));
        $this->cash_to_bank=htmlspecialchars(strip_tags($this->cash_to_bank));
        $this->credit_cards=htmlspecialchars(strip_tags($this->credit_cards));
        $this->operating_expenses=htmlspecialchars(strip_tags($this->operating_expenses));
        $this->volunteer_expenses=htmlspecialchars(strip_tags($this->volunteer_expenses));
        $this->cash_difference=htmlspecialchars(strip_tags($this->cash_difference));
        $this->comments=htmlspecialchars(strip_tags($this->comments));
        $this->quickbooks=htmlspecialchars(strip_tags($this->quickbooks));
        $this->rags_paid_in_cash=htmlspecialchars(strip_tags($this->rags_paid_in_cash));

        // bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":shopid", $this->shopid);
        $stmt->bindParam(":clothing_num", $this->clothing_num);
        $stmt->bindParam(":brica_num", $this->brica_num);
        $stmt->bindParam(":books_num", $this->books_num);
        $stmt->bindParam(":linens_num", $this->linens_num);
        $stmt->bindParam(":donations_num", $this->donations_num);
        $stmt->bindParam(":other_num", $this->other_num);
        $stmt->bindParam(":rag_num", $this->rag_num);
        $stmt->bindParam(":clothing", $this->clothing);
        $stmt->bindParam(":brica", $this->brica);
        $stmt->bindParam(":books", $this->books);
        $stmt->bindParam(":linens", $this->linens);
        $stmt->bindParam(":donations", $this->donations);
        $stmt->bindParam(":other", $this->other);
        $stmt->bindParam(":rag", $this->rag);
        $stmt->bindParam(":customers_num_total", $this->customers_num_total);
        $stmt->bindParam(":cash_to_bank", $this->cash_to_bank);
        $stmt->bindParam(":credit_cards", $this->credit_cards);
        $stmt->bindParam(":operating_expenses", $this->operating_expenses);
        $stmt->bindParam(":volunteer_expenses", $this->volunteer_expenses);
        $stmt->bindParam(":cash_difference", $this->cash_difference);
        if($this->comments == '') {            
            $stmt->bindParam(":comments", $this->null, PDO::PARAM_STR);
        }
        else {
            $stmt->bindParam(":comments", $this->comments);
        }
        $stmt->bindParam(":quickbooks", $this->quickbooks);
        $stmt->bindParam(":rags_paid_in_cash", $this->rags_paid_in_cash);
        
        return $stmt->execute();
    }

    function delete(){
        $query = "DELETE FROM " . $this->table_name . " WHERE takingsid = ?";

        $stmt = $this->conn->prepare($query);
        $this->id=htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        // execute query
        if($stmt->execute()){
            return true;
        }

        return false;
    }

    function patch_quickbooks(){
        $query = "UPDATE
                    " . $this->table_name . "
                    SET 
                    quickbooks=:quickbooks,
                    timestamp=NULL 
                WHERE 
                    takingsid=:id;";

        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->quickbooks=htmlspecialchars(strip_tags($this->quickbooks));

        // bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":quickbooks", $this->quickbooks);
        
        return $stmt->execute();
    }

    function pass_row_data($row) {

        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);

        return array(
            "id" => $id,
            "date" => $date,
            "shopid" => $shopid,
            "clothing_num" => $clothing_num,
            "brica_num" => $brica_num,
            "books_num" => $books_num,
            "linens_num" => $linens_num,
            "donations_num" => $donations_num,
            "other_num" => $other_num,
            "rag_num" => $rag_num,
            "clothing" => $clothing,
            "brica" => $brica,
            "books" => $books,
            "linens" => $linens,
            "donations" => $donations,
            "other" => $other,
            "rag" => $rag,
            "customers_num_total" => $customers_num_total,
            "cash_to_bank" => $cash_to_bank,
            "credit_cards" => $credit_cards,
            "operating_expenses" => $operating_expenses,
            "volunteer_expenses" => $volunteer_expenses,
            "other_adjustments" => $other_adjustments,
            "cash_to_charity" => $cash_to_charity,
            "cash_difference" => $cash_difference,
            "comments" => $comments,
            "rags_paid_in_cash" => $rags_paid_in_cash,
            "quickbooks" => $quickbooks,
            "timestamp" => $timestamp
        );
    }
}