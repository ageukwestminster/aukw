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
   * The name of the employee
   *
   * @var string
   */
  protected string $employeeName;
  /**
   * Gross salary for the month, in pence  
   *
   * @var int
   */
  protected int $totalPay;
    /**
   * Income tax for the month, in pence  
   *
   * @var int
   */
  protected int $paye;
  /**
   * Employee NI for the month, in pence  
   *
   * @var int
   */
  protected int $employeeNI;
    /**
   * Other deductions for the month, in pence  
   *
   * @var int
   */
  protected int $otherDeductions;
    /**
   * Salary sacrificed towards pension for the month, in pence  
   *
   * @var int
   */
  protected int $salarySacrifice=0;
    /**
   * Gross salary for the month, in pence  
   *
   * @var int
   */
  protected int $studentLoan;
    /**
   * Net salary for the month, in pence  
   *
   * @var int
   */
  protected int $netPay;
    /**
   * Employer's NI for the month, in pence  
   *
   * @var int
   */
  protected int $employerNI;
  /**
   * Pension contribution from employer, in pence  
   *
   * @var int
   */
  protected int $employerPension=0;
  /**
   * Pension contribution from employee, in pence  
   *
   * @var int
   */
  protected int $employeePension=0;
 
    /**
   * Employee ID getter
   */
  public function getEmployeeId():int {
    return $this->employeeId;
  }
  /**
   * Employee name getter
   */
  public function getEmployeeName():string {
    return $this->employeeName;
  }
  /**
   * Pension contribution from employee for the month getter. Amount is in pence.
   */
  public function getEmployeePension():int {
    return $this->employeePension;
  }
    /**
   * Pension contribution from employer getter. Amount is in pence.
   */
  public function getEmployerPension():int {
    return $this->employerPension;
  }
    /**
   * Employer NI getter. Amount is in pence.
   */
  public function getEmployerNI():int {
    return $this->employerNI;
  }
    /**
   * Employee NI getter. Amount is in pence.
   */
  public function getEmployeeNI():int {
    return $this->employeeNI;
  }
    /**
   * Net pay for the month getter. Amount is in pence.
   */
  public function getNetPay():int {
    return $this->netPay;
  }
    /**
   * Student loan repayment getter. Amount is in pence.
   */
  public function getStudentLoan():int {
    return $this->studentLoan;
  }  /**
  * Total pay for the month getter. Amount is in pence.
  */
 public function getPAYE():int {
   return $this->paye;
 }
   /**
   *Other deductions getter. Amount is in pence.
   */
  public function getOtherDeductions():int {
    return $this->otherDeductions;
  }
  /**
   * Salary sacrifice getter. Amount is in pence.
   */
  public function getSalarySacrifice():int {
    return $this->salarySacrifice;
  }
    /**
   * Total pay getter. Amount is in pence.
   */
  public function getTotalPay(): int {
    return $this->totalPay;
  }
  /**
   * Employee ID setter
   */
  public function setEmployeeId(int $employeeId) {
    $this->employeeId = $employeeId;
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
   * Pension contribution from employee for the month setter. Amount is in pence.
   */
  public function setEmployeePension(int $employeePension) {
    $this->employeePension = $employeePension;
    return $this;
  }
    /**
   * Pension contribution from employer setter. Amount is in pence.
   */
  public function setEmployerPension(int $employerPension) {
    $this->employerPension = $employerPension;
    return $this;
  }
    /**
   * Employer NI setter. Amount is in pence.
   */
  public function setEmployerNI(int $employerNI) {
    $this->employerNI = $employerNI;
    return $this;
  }
    /**
   * Employee NI setter. Amount is in pence.
   */
  public function setEmployeeNI(int $employeeNI) {
    $this->employeeNI = $employeeNI;
    return $this;
  }
    /**
   * Net pay for the month setter. Amount is in pence.
   */
  public function setNetPay(int $netPay) {
    $this->netPay = $netPay;
    return $this;
  }
    /**
   * Student loan repayment setter. Amount is in pence.
   */
  public function setStudentLoan(int $studentLoan) {
    $this->studentLoan = $studentLoan;
    return $this;
  }  /**
  * Total pay for the month setter. Amount is in pence.
  */
 public function setPAYE(int $paye) {
   $this->paye = $paye;
   return $this;
 }
   /**
   *Other deductions setter. Amount is in pence.
   */
  public function setOtherDeductions(int $otherDeductions) {
    $this->otherDeductions = $otherDeductions;
    return $this;
  }
  /**
   * Salary sacrifice setter. Amount is in pence.
   */
  public function setSalarySacrifice(int $salarySacrifice) {
    $this->salarySacrifice = $salarySacrifice;
    return $this;
  }
    /**
   * Total pay setter. Amount is in pence.
   */
  public function setTotalPay(int $totalPay) {
    $this->totalPay = $totalPay;
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