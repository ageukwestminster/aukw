<?php

namespace Models;

use DateTime;
use Exception;
use Models\Payslip;

/**
 * The Payroll CSV file, as provided by our payroll software company, Iris FMP.
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


  public function parse(): bool {
    if (!isset($this->grossToNetWorkSheet)) {
      $this->parse_worksheets();
    }
    if ($this->parseGrosstoNet()) {
        
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

  private function parseGrosstoNet(): bool {
    $salaryData   = $this->grossToNetWorkSheet->toArray();

    // Loop through employees, creating payslips
    $this->payslips = array();
    for ($i=1; $i < count($salaryData); $i++) { 
      $payrollNumber = (int) trim($salaryData[$i][0]); // '0' = column A
      $payslip = Payslip::getInstance()
        ->setPayrollNumber($payrollNumber) 
        ->setEmployeeName(trim($salaryData[$i][1])) // '1' = column B
        ->setPayrollDate(date('Y-m-d'))
        ->setTotalPay(round( ( (float)$salaryData[$i][4] ) - ((float)$salaryData[$i][15]),2))
        ->setPAYE(-round(((float)$salaryData[$i][7]),2))
        ->setEmployeeNI(-round(((float)$salaryData[$i][8]),2))
        ->setOtherDeductions(-round(((float)$salaryData[$i][14]),2))
        ->setStudentLoan(-round(((float)$salaryData[$i][12]),2))
        ->setNetPay(round(((float)$salaryData[$i][6]),2))
        ->setEmployerNI(round(((float)$salaryData[$i][9]),2))
        ->setEmployeePension(round(((float)$salaryData[$i][10])+((float)$salaryData[$i][15]),2))
        ->setEmployerPension(round(((float)$salaryData[$i][11]),2))
        ->setSalarySacrifice(-round(((float)$salaryData[$i][15]),2));

        $this->payslips[$payrollNumber] = $payslip;
    }    
    return true;
  }
  
}