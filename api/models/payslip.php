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
   * Gross salary for the month.  
   *
   * @var float
   */
  protected float $totalPay;
    /**
   * Income tax for the month.  
   *
   * @var float
   */
  protected float $paye;
  /**
   * Employee NI for the month.  
   *
   * @var float
   */
  protected float $employeeNI;
    /**
   * Other deductions for the month.  
   *
   * @var float
   */
  protected float $otherDeductions;
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
  protected float $studentLoan;
    /**
   * Net salary for the month.  
   *
   * @var float
   */
  protected float $netPay;
    /**
   * Employer's NI for the month.  
   *
   * @var float
   */
  protected float $employerNI;
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
   * Employee name getter
   */
  public function getEmployeeName():string {
    return $this->employeeName;
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
   * Employee name setter
   */
  public function setEmployeeName(string $employeeName) {
    $this->employeeName = $employeeName;
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