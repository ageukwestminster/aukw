<?php

namespace Models;

use DateTime;
use Exception;
use Models\Payslip;

/**
 * The Payroll spreadsheet, as provided by our payroll software company, Iris FMP.
 * 
 * Makes heavy use of PHPSpreadsheet. {@link https://phpspreadsheet.readthedocs.io/en/latest/ }
 * 
 * @category Model
 */
class PayrollBase{

  /**
   * The file path to the payroll data spreadsheet,
   * including full file name.
   *
   * @var string
   */
  protected string $filePath;

  /**
   * The employee payslips
   *
   * @var Array
   */
  protected Array $payslips;

  /**
   * The payroll payment date, give in cell A5 of Summary sheet
   *
   * @var DateTime
   */
  protected DateTime $paymentDate;
 
  /**
   * Encrypted File Path setter
   */
  public function setFilePath(string $filePath) {
    $this->filePath = $filePath;
    return $this;
  }

    /**
   * Payment date setter
   */
  public function setPaymentDate(string $paymentDate) {
    $this->paymentDate = new DateTime($paymentDate);
    return $this;
  }
  /**
   * Payment date getter
   * @return DateTime
   */
  public function getPaymentDate():DateTime {
    return $this->paymentDate;
  }

  /**
   * Payslips getter
   * @return Array
   */
  public function getPayslips():Array {
    // array_values converts from ['1':{}, '2':{}, ...] to [{},{}...]
    return array_values($this->payslips);
  }

  /**
   * Constructor
   */
  protected function __construct(){}


  public function parse(string $payrollDate = ''): bool {
    return false;
  }


}