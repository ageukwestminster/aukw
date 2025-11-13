<?php
namespace Models;
use PDO;
use Exception;
/**
 * Defines a shop and has data persistance capbility.
 * 
 * @category Model
 */
class Allocation{
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
   * The QBO id of the Quickbooks Employee.
   *
   * @var int
   */
  protected int $quickbooksId;
  /**
   * The number of the employee. This is used in Payroll, to link the Iris salary calcualtions to the employee.
   *
   * @var int
   */
  protected int $payrollNumber;
  /**
   * Percentage allocation of salary to specified class
   *
   * @var int
   */
  protected int $percentage;
  /**
   * QBO account ID
   *
   * @var int
   */
  protected int $account;
  /**
   * QBO classs ID
   *
   * @var string
   */
  protected string $class;
  /**
   * True if the allocation to a cost that needs to be charged back to the shop.
   *
   * @var bool
   */
  protected bool $isShopEmployee;

  /**
   * quickbooksId setter.
   */
  public function setQuickbooksId(int $quickbooksId) {
    $this->quickbooksId = $quickbooksId;
    return $this;
  }

  /**
   * payrollNumber setter.
   */
  public function setPayrollNumber(int $payrollNumber) {
    $this->payrollNumber = $payrollNumber;
    return $this;
  }

  /**
   * percentage setter.
   */
  public function setPercentage(int $percentage) {
    $this->percentage = $percentage;
    return $this;
  }

    /**
   * account setter.
   */
  public function setAccount(int $account) {
    $this->account = $account;
    return $this;
  }

  /**
   * class setter.
   */
  public function setClass(string $class) {
    $this->class = $class;
    return $this;
  }

    /**
   * isShopEmployee setter.
   */
  public function setIsShopEmployee(bool $isShopEmployee) {
    $this->isShopEmployee = $isShopEmployee;
    return $this;
  }

  /**
   * quickbooksId getter.
   */
  public function getQuickbooksId() : int {
    return $this->quickbooksId;
  }

  /**
   * percentage getter.
   */
  public function getPercentage() : int {
    return $this->percentage;
  }  

  /**
   * payrollNumber getter.
   */
  public function getPayrollNumber() : int {
    return $this->payrollNumber;
  }

    /**
   * account getter.
   */
  public function getAccount() : int {
    return $this->account;
  }

    /**
   * class getter.
   */
  public function getClass() : string {
    return $this->class;
  }
  
  /**
   * isShopEmployee getter.
   */
  public function getIsShopEmployee() : bool {
    return $this->isShopEmployee;
  }

  /**
   * Constructor
   */
  protected function __construct(){
    $this->conn = \Core\Database::getInstance()->conn;
  }

  /**
   * Static constructor / factory
   */
  public static function getInstance() {    
    return new self();
  }


  /**
   * Retrieve from the database details of the User, specified by
   * First Name, Surname and Email address
   * 
   * @return void
   * 
   */
  public function readOne(){

    $query = "SELECT " .
        " `quickbooksId`, `payrollNumber`, `percentage`, `account`, `class`, `isShopEmployee`
      FROM
          " . $this->table_name . "
      WHERE 
          quickbooksId = :quickbooksId AND class = :class
      LIMIT 0,1";

    $stmt = $this->conn->prepare( $query );
    // sanitize
    $this->quickbooksId=filter_var($this->quickbooksId, FILTER_SANITIZE_NUMBER_INT);
    $this->class=htmlspecialchars(strip_tags($this->class ?? ''));
    // bind values
    $stmt->bindParam(":class", $this->class);
    $stmt->bindParam(":quickbooksId", $this->quickbooksId, PDO::PARAM_INT);
    $stmt->execute();

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
      // set values to object properties
      $this->payrollNumber = $row['payrollNumber'];
      $this->percentage = $row['percentage'];
      $this->account = $row['account'];
      $this->isShopEmployee = $row['isShopEmployee'] == 1 ? true : false;

      return [
        "quickbooksId" => $this->quickbooksId,
        "payrollNumber" => $this->payrollNumber,
        "percentage"=>$this->percentage,
        "account"=>$this->account,
        "class"=>$this->class,
        "isShopEmployee"=>$this->isShopEmployee
    ];
    } else {
      return [];
    }


  }

    /**
   * Add a single new Allocation record to the database.
   * @return true 'true' if database insert succeeded.
   * @throws PDOException 
   * @throws Exception 
   */
  function create():true{
    $query = "INSERT INTO
                " . $this->table_name . "
                SET 
                quickbooksId=:quickbooksId,
                payrollNumber=:payrollNumber, 
                percentage=:percentage,
                account=:account,
                class=:class,
                isShopEmployee=:isShopEmployee";
    
    // prepare query
    $stmt = $this->conn->prepare($query);

    // sanitize
    $this->quickbooksId=filter_var($this->quickbooksId, FILTER_SANITIZE_NUMBER_INT);
    $this->payrollNumber=filter_var($this->payrollNumber, FILTER_SANITIZE_NUMBER_INT);
    $this->percentage=filter_var($this->percentage, FILTER_SANITIZE_NUMBER_INT);
    $this->account=filter_var($this->account, FILTER_SANITIZE_NUMBER_INT);
    $this->class=htmlspecialchars(strip_tags($this->class ?? ''));

    // Convert supplied values to tinyint for database
    $isShopEmployee = $this->isShopEmployee ? 1 : 0;

    // bind values
    $stmt->bindParam(":class", $this->class);
    $stmt->bindParam(":isShopEmployee", $isShopEmployee, PDO::PARAM_INT);
    $stmt->bindParam(":quickbooksId", $this->quickbooksId, PDO::PARAM_INT);
    $stmt->bindParam(":payrollNumber", $this->payrollNumber, PDO::PARAM_INT);
    $stmt->bindParam(":percentage", $this->percentage, PDO::PARAM_INT);
    $stmt->bindParam(":account", $this->account, PDO::PARAM_INT);

    
    // execute query
    if ($stmt->execute()){
      return true;
    } else {
        throw new Exception("Unable to add user to database.");
    }
  } 

  /**
   * Update an existing Allocation in the database with new data.
   * 
   * @return bool 'true' if database update succeeded.
   * 
   */
  function update():bool{
    $query = "UPDATE
                " . $this->table_name . "
                SET 
                percentage=:percentage,
                account=:account,
                isShopEmployee=:isShopEmployee"
              . " WHERE quickbooksId = :quickbooksId"
              . " AND payrollNumber = :payrollNumber"
              . " AND class = :class";
    
    // prepare query
    $stmt = $this->conn->prepare($query);

    // sanitize
    $this->quickbooksId=filter_var($this->quickbooksId, FILTER_SANITIZE_NUMBER_INT);
    $this->payrollNumber=filter_var($this->payrollNumber, FILTER_SANITIZE_NUMBER_INT);
    $this->percentage=filter_var($this->percentage, FILTER_SANITIZE_NUMBER_INT);
    $this->account=filter_var($this->account, FILTER_SANITIZE_NUMBER_INT);
    $this->class=htmlspecialchars(strip_tags($this->class ?? ''));

    // Convert supplied values to tinyint for database
    $isShopEmployee = $this->isShopEmployee ? 1 : 0;

    // bind values
    $stmt->bindParam(":class", $this->class);
    $stmt->bindParam(":isShopEmployee", $isShopEmployee, PDO::PARAM_INT);
    $stmt->bindParam(":quickbooksId", $this->quickbooksId, PDO::PARAM_INT);
    $stmt->bindParam(":payrollNumber", $this->payrollNumber, PDO::PARAM_INT);
    $stmt->bindParam(":percentage", $this->percentage, PDO::PARAM_INT);
    $stmt->bindParam(":account", $this->account, PDO::PARAM_INT);

    return $stmt->execute();
  }
}