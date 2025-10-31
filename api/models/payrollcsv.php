<?php

namespace Models;

use DateTime;
use Exception;
use Models\Payslip;

/**
 * The Payroll CSV file, as provided by our payroll software company, Iris FMP.
 * 
 * This class includes the business logic required to extract the relevant payroll 
 * data (net pay, tax etc.) per employee from the CSV file, aka parsing the file.
 * 
 * @category Model
 */
class PayrollCsv extends PayrollBase{

  /**
   * The WorkSheet object for the GrossToNet sheet
   *
   * @var object
   */
  protected object $grossToNetWorkSheet;

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


  public function parse(string $payrollDate = ''): bool {
    if (!isset($this->grossToNetWorkSheet)) {
      $this->parse_worksheets();
    }
    if ($this->parseGrosstoNet($payrollDate)) {
        
        unset($this->grossToNetWorkSheet);
        return true;
    }
    return false;
  }

    /**
   * Open the spreadsheet file specified in the FilePath property and
   * store references to the pensions and summary worksheets.
   * @return object list of worksheet namesc
   */
  public function parse_worksheets() {

    /**  Create a new Reader of type 'csv' **/
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');
    /**  Advise the Reader that we only want to load cell data  **/
    $reader->setReadDataOnly(true);
    /**  Load $inputFileName to a Spreadsheet Object  **/
    $spreadsheet = $reader->load($this->filePath);

    $worksheets = array();

    $this->grossToNetWorkSheet = $spreadsheet->getActiveSheet();

    $worksheets['grossToNet'] = $this->grossToNetWorkSheet->getTitle();

    return $worksheets;
  }

  /**
   * Parse the Gross to Net worksheet, creating payslips for each employee.
   * @param string $payrollDate 
   * @return bool 
   * @throws Exception 
   */
  private function parseGrosstoNet(string $payrollDate = ''): bool {

    // Set payment date using supplied parameter, or default to today
    if ($payrollDate != '') {
      if (DateTime::createFromFormat('Y-m-d', $payrollDate) !== false) {
        $this->paymentDate = DateTime::createFromFormat('Y-m-d', $payrollDate);
      } else if (DateTime::createFromFormat('d/m/Y', $payrollDate) !== false) {
        $this->paymentDate = DateTime::createFromFormat('d/m/y', $payrollDate);
      } else {
        throw new Exception('Unable to set date from supplied http parameter value: "'. $payrollDate . '." .
          " Try entering the date in the format year-month-day or day/month/year.');
      }  
    } else {
      $this->paymentDate = DateTime::createFromFormat('Y-m-d', date('Y-m-d'));
    }

    $salaryData   = $this->grossToNetWorkSheet->toArray();

    // Loop through employees, creating payslips
    $this->payslips = array();
    for ($i=1; $i < count($salaryData); $i++) { 
      $payrollNumber = (int) trim($salaryData[$i][0]); // '0' = column A

      // We are always rounding the numbers to 2 decimal places to avoid floating point precision issues
      $totalPay = round((float) trim($salaryData[$i][4]),2);
      $netPay = round((float) trim($salaryData[$i][6]),2);
      $paye = round((float) trim($salaryData[$i][7]),2);
      $employeeNI = round((float) trim($salaryData[$i][8]),2);
      $employerNI = round((float) trim($salaryData[$i][9]),2);
      $employeePension = round((float) trim($salaryData[$i][10]),2);
      $employerPension = round((float) trim($salaryData[$i][11]),2);
      $studentLoan = round((float) trim($salaryData[$i][12]),2);
      $statutoryPayments = round((float) trim($salaryData[$i][13]),2); // e.g. SSP, SMP
      $attachments = round((float) trim($salaryData[$i][14]),2); // e.g. court orders
      $otherDeductions = round((float) trim($salaryData[$i][15]),2);

      // Calculate Salary Sacrifice by determining how net pay compares to the expected amount.
      $salarySacrifice = round(( $totalPay + $employeePension) - 
                              ( $netPay +
                                $paye + 
                                $employeeNI +
                                $studentLoan +
                                $attachments +
                                $statutoryPayments +
                                $otherDeductions)
                              , 2);
                              
      // the employee pension variable is only for genuine out-of-pay contributions, not salary sacrifice
      // so reduce it by the salary sacrifice amount.
      $employeePension -= $salarySacrifice;

      $payslip = Payslip::getInstance()
        ->setPayrollNumber($payrollNumber) 
        ->setEmployeeName(trim($salaryData[$i][1])) // '1' = column B
        ->setPayrollDate($this->paymentDate->format('Y-m-d'))
        ->setTotalPay(round($totalPay + $salarySacrifice,2))
        ->setPAYE(-$paye)
        ->setEmployeeNI(-$employeeNI)
        ->setOtherDeductions(round(-$statutoryPayments-$attachments-$otherDeductions,2))
        ->setStudentLoan(-$studentLoan)
        ->setNetPay($netPay)
        ->setEmployerNI($employerNI)
        ->setEmployeePension($employeePension)
        ->setEmployerPension($employerPension)
        ->setSalarySacrifice($salarySacrifice);

        // Check that the payslip is in balance
        if (!$payslip->isBalanced()) {
          $imbalance = $payslip->getImbalanceAmount() ? $payslip->getImbalanceAmount() : 'N/A';
          throw new Exception(
            'Payslip for ' . $payslip->getEmployeeName() .
            ' with payroll number ' . $payrollNumber . ' is not balanced. ' .
            'Imbalance: ' . $imbalance . '. ' .
            'Values: TotalPay=' . $payslip->getTotalPay() .
            ', PAYE=' . $payslip->getPAYE() .
            ', EmployeeNI=' . $payslip->getEmployeeNI() .
            ', OtherDeductions=' . $payslip->getOtherDeductions() .
            ', StudentLoan=' . $payslip->getStudentLoan() .
            ', NetPay=' . $payslip->getNetPay() .
            ', EmployerNI=' . $payslip->getEmployerNI() .
            ', EmployeePension=' . $payslip->getEmployeePension() .
            ', EmployerPension=' . $payslip->getEmployerPension() .
            ', SalarySacrifice=' . $payslip->getSalarySacrifice()
          );
        }

        $this->payslips[$payrollNumber] = $payslip;
    }    
    return true;
  }
  
}