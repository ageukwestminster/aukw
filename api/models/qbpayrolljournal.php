<?php

namespace Models;

use Core\QuickbooksConstants as QBO;
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
     * The total amount of salary, split into allocations
     *
     * @var Array
     */
    protected Array $grossSalary;  

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
     * Employee Number (QBO) setter.
     */
    public function setEmployeeNumber(string $employeeNumber) {
      $this->employeeNumber = $employeeNumber;
      return $this;
    }

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
     * Gross Salary setter.
     */
    public function setGrossSalary(Array $grossSalary) {
      $this->grossSalary = $grossSalary;
      return $this;
    }

    /**
     * Net Salary setter.
     */
    public function setNetSalary(float $netSalary) {
      $this->netSalary = $netSalary;
      return $this;
    }
    /**
     * PAYE (income tax) setter.
     */
    public function setPAYE(float $paye) {
      $this->paye = $paye;
      return $this;
    }
    
    /**
     * Employee NI setter.
     */
    public function setEmployeeNI(float $employeeNI) {
      $this->employeeNI = $employeeNI;
      return $this;
    }

    /**
     * Other Deductions setter.
     */
    public function setOtherDeduction(float $otherDeduction) {
      $this->otherDeduction = $otherDeduction;
      return $this;
    }
    /**
     * Salary Sacrifice setter.
     */
    public function setSalarySacrifice(float $salarySacrifice) {
      $this->salarySacrifice = $salarySacrifice;
      return $this;
    }
    /**
     * Student Loan setter.
     */
    public function setStudentLoan(float $studentLoan) {
      $this->studentLoan = $studentLoan;
      return $this;
    }

    /**
     * Pension contribution from employee for the month setter.
     */
    public function setEmployeePension(float $employeePensionContribution) {
      $this->employeePensionContribution = $employeePensionContribution;
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

        // For each line below it will only add the respective line if amount != 0

        foreach($this->grossSalary as $grossSalaryAllocation) {
          //&$line_array, $description, $amount, $item, $class, $quantity, $account, $taxcoderef)
          $this->payrolljournal_line($payrolljournal['Line'], "Gross Salary", 
            $grossSalaryAllocation->amount, $this->employeeNumber, 
            $grossSalaryAllocation->class,$grossSalaryAllocation->account);
        }

        $this->payrolljournal_line($payrolljournal['Line'], "PAYE", 
          $this->paye, $this->employeeNumber, QBO::ADMIN_CLASS,
          QBO::TAX_ACCOUNT);

        $this->payrolljournal_line($payrolljournal['Line'], "Employee NI", 
          $this->employeeNI, $this->employeeNumber, QBO::ADMIN_CLASS,
          QBO::TAX_ACCOUNT);

        $this->payrolljournal_line($payrolljournal['Line'], "Salary Sacrifice", 
          $this->salarySacrifice, $this->employeeNumber, QBO::ADMIN_CLASS,
          QBO::SALARY_SACRIFICE_ACCOUNT);

        $this->payrolljournal_line($payrolljournal['Line'], "Employee Pension Contribution", 
          $this->employeePensionContribution, $this->employeeNumber, QBO::ADMIN_CLASS,
          QBO::EMPLOYEE_PENSION_CONTRIB_ACCOUNT);

        $this->payrolljournal_line($payrolljournal['Line'], "Other Deductions", 
          $this->otherDeduction, $this->employeeNumber, QBO::ADMIN_CLASS,
          QBO::OTHER_DEDUCTIONS_ACCOUNT);

        $this->payrolljournal_line($payrolljournal['Line'], "Student Loan Deductions", 
          $this->studentLoan, $this->employeeNumber, QBO::ADMIN_CLASS,
          QBO::TAX_ACCOUNT);     

        $this->payrolljournal_line($payrolljournal['Line'], "Net Pay", 
            $this->netSalary, $this->employeeNumber, QBO::ADMIN_CLASS,
            QBO::NET_PAY_ACCOUNT);


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
            $line->amount, $line->employeeId, $line->class, $line->account);
        
        $sum -= $line->amount;
      }

      $this->payrolljournal_line($payrolljournal['Line'], "", 
        $sum, '', QBO::ADMIN_CLASS, QBO::TAX_ACCOUNT);
    
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
   * Check the provided values make sense. Is transaction in balance?
   */
  public function validate(): bool {

    if (!$this->grossSalary || !count($this->grossSalary)) return false;

    // Sum of Gross Salary
    $grossSalary = 0;
    foreach ($this->grossSalary as $salaryAllocation) {
      $grossSalary += $salaryAllocation->amount;
    }
    
    $balance = $grossSalary+$this->paye+$this->employeeNI+$this->otherDeduction
                    +$this->employeePensionContribution
                    +$this->salarySacrifice+$this->studentLoan+$this->netSalary; 
    
    if (abs($balance) >= 0.005) {
      return false;
    }    

    return true;
  }

}