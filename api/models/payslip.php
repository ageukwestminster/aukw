<?php

namespace Models;


/**
 * Store and manipulate payroll data for a single employee
 * 
 * 
 * @category Model
 */
class Payslip implements \JsonSerializable{

  /**
   * The ID of the employee
   *
   * @var int
   */
  protected int $employeeId;
    /**
   * The ID of the employee on QBO
   *
   * @var int
   */
  protected int $quickbooksId;
  /**
   * The name of the employee
   *
   * @var string
   */
  protected string $employeeName;
  /**
   * The date of the payroll run
   *
   * @var string
   */
  protected string $payrollDate;
  /**
   * Gross salary for the month.  
   *
   * @var float
   */
  protected float $totalPay=0;
    /**
   * Income tax for the month.  
   *
   * @var float
   */
  protected float $paye=0;
  /**
   * Employee NI for the month.  
   *
   * @var float
   */
  protected float $employeeNI=0;
    /**
   * Other deductions for the month.  
   *
   * @var float
   */
  protected float $otherDeductions=0;
    /**
   * Salary sacrificed towards pension for the month.  
   *
   * @var float
   */
  protected float $salarySacrifice=0;
    /**
   * Gross salary for the month.  
   *
   * @var float
   */
  protected float $studentLoan=0;
    /**
   * Net salary for the month.  
   *
   * @var float
   */
  protected float $netPay=0;
    /**
   * Employer's NI for the month.  
   *
   * @var float
   */
  protected float $employerNI=0;
  /**
   * Pension contribution from employer.  
   *
   * @var float
   */
  protected float $employerPension=0;
  /**
   * Pension contribution from employee.  
   *
   * @var float
   */
  protected float $employeePension=0;
 
  /**
   * Employee ID getter
   */
  public function getEmployeeId():int {
    return $this->employeeId;
  }
  /**
   * Quickbooks ID getter
   */
  public function getQuickbooksId():int {
    return $this->quickbooksId;
  }  
  /**
   * Employee name getter
   */
  public function getEmployeeName():string {
    return $this->employeeName;
  }
  /**
   * Payroll run date getter
   */
  public function getPayrollDate():int {
    return $this->payrollDate;
  }  
  /**
   * Pension contribution from employee for the month getter.
   */
  public function getEmployeePension():float {
    return $this->employeePension;
  }
    /**
   * Pension contribution from employer getter.
   */
  public function getEmployerPension():float {
    return $this->employerPension;
  }
    /**
   * Employer NI getter.
   */
  public function getEmployerNI():float {
    return $this->employerNI;
  }
    /**
   * Employee NI getter.
   */
  public function getEmployeeNI():float {
    return $this->employeeNI;
  }
    /**
   * Net pay for the month getter.
   */
  public function getNetPay():float {
    return $this->netPay;
  }
    /**
   * Student loan repayment getter.
   */
  public function getStudentLoan():float {
    return $this->studentLoan;
  }  /**
  * Total pay for the month getter.
  */
 public function getPAYE():float {
   return $this->paye;
 }
   /**
   *Other deductions getter.
   */
  public function getOtherDeductions():float {
    return $this->otherDeductions;
  }
  /**
   * Salary sacrifice getter.
   */
  public function getSalarySacrifice():float {
    return $this->salarySacrifice;
  }
    /**
   * Total pay getter.
   */
  public function getTotalPay(): float {
    return $this->totalPay;
  }
  /**
   * Employee ID setter
   */
  public function setEmployeeId(float $employeeId) {
    $this->employeeId = $employeeId;
    return $this;
  }
  /**
   * Quickbooks employee ID setter
   */
  public function setQuickbooksId(float $quickbooksId) {
    $this->quickbooksId = $quickbooksId;
    return $this;
  }  
  /**
   * Employee name setter
   */
  public function setEmployeeName(string $employeeName) {
    $this->employeeName = $employeeName;
    return $this;
  }
  /**
   * Payroll date setter
   */
  public function setPayrollDate(string $payrollDate) {
    $this->payrollDate = $payrollDate;
    return $this;
  }  
  /**
   * Pension contribution from employee for the month setter.
   */
  public function setEmployeePension(float $employeePension) {
    $this->employeePension = $employeePension;
    return $this;
  }
    /**
   * Pension contribution from employer setter.
   */
  public function setEmployerPension(float $employerPension) {
    $this->employerPension = $employerPension;
    return $this;
  }
    /**
   * Employer NI setter.
   */
  public function setEmployerNI(float $employerNI) {
    $this->employerNI = $employerNI;
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
   * Net pay for the month setter.
   */
  public function setNetPay(float $netPay) {
    $this->netPay = $netPay;
    return $this;
  }
    /**
   * Student loan repayment setter.
   */
  public function setStudentLoan(float $studentLoan) {
    $this->studentLoan = $studentLoan;
    return $this;
  }  /**
  * Total pay for the month setter.
  */
 public function setPAYE(float $paye) {
   $this->paye = $paye;
   return $this;
 }
   /**
   *Other deductions setter.
   */
  public function setOtherDeductions(float $otherDeductions) {
    $this->otherDeductions = $otherDeductions;
    return $this;
  }
  /**
   * Salary sacrifice setter.
   */
  public function setSalarySacrifice(float $salarySacrifice) {
    $this->salarySacrifice = $salarySacrifice;
    return $this;
  }
  /**
   * Total pay setter.
   */
  public function setTotalPay(float $totalPay) {
    $this->totalPay = $totalPay;
    return $this;
  }
  /**
   * Increment Pension contribution from employee for the month by the given amount.
   */
  public function addToEmployeePension(float $employeePension) {
    $this->employeePension += $employeePension;
    $this->employeePension = round($this->employeePension, 2);
    return $this;
  }
    /**
   * Increment Pension contribution from employer by the given amount.
   */
  public function addToEmployerPension(float $employerPension) {
    $this->employerPension += $employerPension;
    $this->employerPension = round($this->employerPension, 2);
    return $this;
  }
    /**
   * Increment Employer NI by the given amount.
   */
  public function addToEmployerNI(float $employerNI) {
    $this->employerNI += $employerNI;
    $this->employerNI = round($this->employerNI, 2);
    return $this;
  }
    /**
   * Increment Employee NI setter by the given amount.
   */
  public function addToEmployeeNI(float $employeeNI) {
    $this->employeeNI += $employeeNI;
    $this->employeeNI = round($this->employeeNI, 2);
    return $this;
  }
    /**
   *  Increment Net pay for the month by the given amount.
   */
  public function addToNetPay(float $netPay) {
    $this->netPay += $netPay;
    $this->netPay = round($this->netPay, 2);
    return $this;
  }
  /**
   * Increment Student loan repayment by the given amount.
   */
  public function addToStudentLoan(float $studentLoan) {
    $this->studentLoan += $studentLoan;
    $this->studentLoan = round($this->studentLoan, 2);
    return $this;
  }  
  /**
  * Total pay for the month by the given amount.
  */
  public function addToPAYE(float $paye) {
    $this->paye += $paye;
    $this->paye = round($this->paye, 2);
    return $this;
  }
   /**
   * Increment Other deductions by the given amount.
   */
  public function addToOtherDeductions(float $otherDeductions) {
    $this->otherDeductions += $otherDeductions;
    $this->otherDeductions = round($this->otherDeductions, 2);
    return $this;
  }
  /**
   * Increment Salary sacrifice by the given amount.
   */
  public function addToSalarySacrifice(float $salarySacrifice) {
    $this->salarySacrifice += $salarySacrifice;
    $this->salarySacrifice = round($this->salarySacrifice, 2);
    return $this;
  }
  /**
   * Increment Total pay by the given amount.
   */
  public function addToTotalPay(float $totalPay) {
    $this->totalPay += $totalPay;
    $this->totalPay = round($this->totalPay, 2);
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

  /**
   * Override the jsonSerialize funciton to list the inner properties
   */
  public function jsonSerialize():mixed
  {
      return get_object_vars($this);
  }


}