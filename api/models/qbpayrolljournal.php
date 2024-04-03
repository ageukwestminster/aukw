<?php

namespace Models;

use QuickBooksOnline\API\Facades\JournalEntry;

/**
 * Factory class that all creation of QB Payroll jopurnal entries
 * 
 * @category Model
 */
class QuickbooksPayrollJournal extends QuickbooksJournal{
    /**
     * The QB employee number
     *
     * @var string
     */
    protected string $employeeNumber; 
    
    /**
     * The transaction date of the Journal entry
     *
     * @var string
     */
    protected string $TxnDate;  

    /**
     * The Reference number for the transaction. Does not have to be unique.
     *
     * @var string
     */
    protected string $DocNumber;  

    /**
     * The total amount of salary.
     *
     * @var float
     */
    protected float $grossSalary;  

    /**
     * The amount actually paid to the employee.
     *
     * @var float
     */
    protected float $netSalary;  

    /**
     * The amount of income tax deducted from the employee gross salary.
     *
     * @var float
     */
    protected float $paye;  

    /**
     * The amount of NI deducted from the employee gross salary.
     *
     * @var float
     */
    protected float $employeeNI;
    
    /**
     * The amount of NI paid by the charity.
     *
     * @var float
     */
    protected float $employerNI;

    /**
     * The student loan repayment deducted from the employee gross salary.
     *
     * @var float
     */
    protected float $studentLoan;

    /**
     * The total amount of any other deduction from the employee gross salary.
     *
     * @var float
     */
    protected float $otherDeduction;

    /**
     * The amount of extra pension contribution made by the employee and deducted from gross salary.
     *
     * @var float
     */
    protected float $employeePensionContribution;

    /**
     * The amount of extra pension contribution made by the employee via a salary sacrifice.
     *
     * @var float
     */
    protected float $salarySacrifice;

    /**
     * Transaction Date setter.
     */
    public function setTxnDate(string $txnDate) {
        $this->TxnDate = $txnDate;
        return $this;
    }

    /**
     * Reference number setter.
     */
    public function setDocNumber(string $docNumber) {
        $this->DocNumber = $docNumber;
        return $this;
    }

    /**
     * Reference number getter.
     */
    public function getDocNumber() : string {
        return $this->DocNumber;
    }

    /**
     * Transaction Date getter.
     */
    public function getrealmId() : string {
        return $this->realmid;
    }

    /**
     * Transaction Date getter.
     */
    public function getTxnDate() : string {
        return $this->TxnDate;
    }


    /**
     * Private realmID setter.
     */
    public function setRealmID(string $realmid) {
        $this->realmid = $realmid;
        return $this;
    }

    /**
     * Constructor
     */
    protected function __construct(){}

    /**
     * Static constructor / factory
     */
    public static function getInstance() {
        return new self();
    }


    public function create_employee_journal() {
        $payrolljournal = array(
            "TxnDate" => $this->TxnDate,
            "DocNumber" => $this->DocNumber,
            "Line" => [],
            "TotalAmt" => 0
        );

        //&$line_array, $description, $amount, $item, $class, $quantity, $account, $taxcoderef)
        // This code will only add the respective line if amount != 0
        $this->payrolljournal_line($payrolljournal['Line'], "Gross Salary", 
            2080, 145, 1400000000000130700,65);
        $this->payrolljournal_line($payrolljournal['Line'], "PAYE", 
            -198, 145, 1400000000000130710 ,256);
        $this->payrolljournal_line($payrolljournal['Line'], "Employee NI", 
            -99.04, 145, 1400000000000130710,256);
        $this->payrolljournal_line($payrolljournal['Line'], "Salary Sacrifice", 
            -41.6, 145, 1400000000000130710,375);
        $this->payrolljournal_line($payrolljournal['Line'], "Net Pay", 
            -1741.36, 145, 1400000000000130710,98);
        $this->payrolljournal_line($payrolljournal['Line'], "Employer NI", 
            176.69, 145, 1400000000000130700,65);
        $this->payrolljournal_line($payrolljournal['Line'], "Employer NI", 
            -176.69, 145, 1400000000000130710,256);


        $theResourceObj = JournalEntry::create($payrolljournal);
    
        $auth = new QuickbooksAuth();
        $dataService = $auth->prepare($this->getrealmId());
        if ($dataService == false) {
          return false;
        }

        $resultingObj = $dataService->Add($theResourceObj);

        $error = $dataService->getLastError();
        if ($error) {
            echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
            echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
            echo "The Response message is: " . $error->getResponseBody() . "\n";
            return false;
        } else {      
          return array(
              "id" => $resultingObj->Id,
              "date" => $this->TxnDate,
              "label" => $this->DocNumber
          );
        }        
    }

    public function create_employerNI_journal($entries) {

      $payrolljournal = array(
          "TxnDate" => $this->TxnDate,
          "DocNumber" => $this->DocNumber,
          "Line" => [],
          "TotalAmt" => 0
      );

      $sum = 0;
      foreach ($entries as $line) {
        //&$line_array, $description, $amount, $employee, $class, $account)
        // This code will only add the respective line if amount != 0
        $this->payrolljournal_line($payrolljournal['Line'], "", 
            $line->amount, $line->employeeId, $line->class,$line->account);
        
        $sum -= $line->amount;
      }

      $this->payrolljournal_line($payrolljournal['Line'], "", 
        $sum, '', $this->admin_class['value'],$this->tax_account['value']);
    
      $theResourceObj = JournalEntry::create($payrolljournal);
  
      $auth = new QuickbooksAuth();
      $dataService = $auth->prepare($this->getrealmId());
      if ($dataService == false) {
        return false;
      }

      $resultingObj = $dataService->Add($theResourceObj);

      $error = $dataService->getLastError();
      if ($error) {
          echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
          echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
          echo "The Response message is: " . $error->getResponseBody() . "\n";
          return false;
      } else {      
        return array(
            "id" => $resultingObj->Id,
            "date" => $this->TxnDate,
            "label" => $this->DocNumber
        );
      }        
  }

  /**
   * Push a new array describing a single line of a QBO journal into the given array
   * Helper function used in create.
   *
   * @param mixed $line_array The given array
   * @param mixed $description
   * @param mixed $amount
   * @param mixed $employee
   * @param mixed $class
   * @param mixed $account
   * 
   * @return void
   * 
   */
  private function payrolljournal_line(&$line_array, $description, $amount, $employee, $class, $account) {
    if (abs($amount) <= 0.005) return;

    array_push($line_array, array(
      "Description" => $description,
      "Amount" => abs($amount),
      "DetailType" => "JournalEntryLineDetail",
      "JournalEntryLineDetail" => [
        "PostingType" => ($amount<0?"Credit":"Debit"),
        "Entity" => [
            "Type" => "Employee",
            "EntityRef" => $employee
        ],
        "AccountRef" => $account,
        "ClassRef" => $class,
      ]
    ));
  }

  
  /**
   * Check the provided values make sense.
   */
  public function validate(): bool {

    // is transaction in balance?
    // Sales = clothing+brica+books+linens+ragging+donations
    // Money Received = cash + creditcards + vol expenses + op expenses
    // Sales must equal Money Received + Cash Discrepancy
    $balance = 0;//$this->donations->sales + $this->clothing->sales + $this->brica->sales;
    //$balance += $this->books->sales + $this->linens->sales + $this->ragging->sales;
    //$balance += $this->cashDiscrepancy + $this->cashToCharity + $this->creditCards;
    //$balance += $this->volunteerExpenses + $this->operatingExpenses + $this->cash;
    
    if (abs($balance) >= 0.005) {
      return false;
    }    

    return true;
  }

  private $unrestricted_class = [
    "value" => 1400000000000130700,
    "name" => "01 Unrestricted"
  ];
  private $admin_class = [
    "value" => 1400000000000130710,
    "name" => "04 Administration"
  ];
  private $tax_account = [
    "value" => 256,
    "name" => "Net Pay & PAYE:Tax and National Insurance"
  ];
}