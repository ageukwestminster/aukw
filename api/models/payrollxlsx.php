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
class PayrollXlsx extends PayrollBase{

  /**
   * The WorkSheet object for the EE Summary sheet
   *
   * @var object
   */
  protected object $summaryWorkSheet;

  /**
   * The WorkSheet object for the Pensions sheet
   *
   * @var object
   */
  protected object $pensionsWorkSheet;

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
    if (!isset($this->pensionsWorkSheet) || !isset($this->summaryWorkSheet)) {
      $this->parse_worksheets();
    }

    if ($this->parseSummary($payrollDate)) {
      if ($this->parsePensions()) {
        
        unset($this->summaryWorkSheet,$this->pensionsWorkSheet);
        return true;
      }
    }
    return false;
  }

  /**
   * Open the spreadsheet file specified in the FilePath property and
   * store references to the pensions and summary worksheets.
   * @return object list of worksheet namesc
   */
  public function parse_worksheets() {

    /**  Create a new Reader of type 'Xlsx' **/
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
    /**  Advise the Reader that we only want to load cell data  **/
    $reader->setReadDataOnly(true);
    /**  Load $inputFileName to a Spreadsheet Object  **/
    $spreadsheet = $reader->load($this->filePath);

    $worksheets = array();

    $names  = $spreadsheet->getSheetNames();
    foreach ($names as $name) {
      if (preg_match('/pensions report/i', $name)) {
        $this->pensionsWorkSheet = $spreadsheet->getSheetByName($name);
        $worksheets['pensions'] = $this->pensionsWorkSheet->getTitle();
      } else if (preg_match('/ee summary/i', $name)) {
        $this->summaryWorkSheet = $spreadsheet->getSheetByName($name);
        $worksheets['summary'] = $this->summaryWorkSheet->getTitle();
      }
    }

    return $worksheets;
  }


  private function parseSummary(string $payrollDate = ''): bool {
    $worksheet = $this->summaryWorkSheet;
    // Get the highest row and column numbers referenced in the worksheet
    $highestRow = $worksheet->getHighestDataRow(); // e.g. 10

    $rowindex=0;

    // First find the payment date that the file was prepared for
    foreach ($worksheet->getRowIterator() as $row) {
      $rowindex++;

      // ignore empty rows
      if(!$row->isEmpty()) { 

        // Looking for a string of format '............dd/mm/yyyy...............' in first column
        $value = $worksheet->getCell([1, $rowindex])->getValue();
        if (preg_match("/\d{1,2}\/\d{1,2}\/\d{2,4}/", $value, $matches)) {
          if (DateTime::createFromFormat('d/m/Y', $matches[0]) !== false) {
            $this->paymentDate = DateTime::createFromFormat('d/m/Y', $matches[0]);
          } else {
            throw new \Exception('Unable to set date from EE Summary sheet. Using "'. $matches[0] . '".');
          }         
          break;
        }
      }
    } // end of payment date search

    // Payment date override
    if ($payrollDate != '') {
      if (DateTime::createFromFormat('Y-m-d', $payrollDate) !== false) {
        $this->paymentDate = DateTime::createFromFormat('Y-m-d', $payrollDate);
      } else {
        throw new Exception('Unable to set date from supplied http parameter value: "'. $payrollDate . '".');
      }  
    }

    // Now find first & last employee record
    $firstEmployeeRow=0;
    while(++$rowindex <= $highestRow) {
      $value = $worksheet->getCell([1, $rowindex])->getValue();
      if ($value) {
        $value = trim($value);
        if (is_numeric($value)) {
          $firstEmployeeRow = $rowindex;
          break;
        }
      }
    }
    $lastEmployeeRow=0;
    while(++$rowindex <= $highestRow) {
      $value = $worksheet->getCell([1, $rowindex])->getValue();
      if (!$value || !is_numeric(trim($value))) {
        $lastEmployeeRow = $rowindex-1;
        break;
      }
    }

    // Convert salary table to PHP array
    $salaryData = $worksheet->rangeToArray(
        'A'.$firstEmployeeRow.':O'.$lastEmployeeRow,     // The worksheet range that we want to retrieve
        NULL,        // Value that should be returned for empty cells
        FALSE,        // Should formulas be calculated (the equivalent of getCalculatedValue() for each cell)
        FALSE,        // Should values be formatted (the equivalent of getFormattedValue() for each cell)
        FALSE         // Should the array be indexed by cell row and cell column
    );

    // Loop through employees, creating payslips
    $this->payslips = array();
    for ($i=0; $i < count($salaryData); $i++) { 
      $payrollNumber = (int) trim($salaryData[$i][0]); // '0' = column A
      $payslip = Payslip::getInstance()
        ->setPayrollNumber($payrollNumber) 
        ->setEmployeeName(trim($salaryData[$i][2])) // '2' = column C
        ->setPayrollDate($this->paymentDate->format('Y-m-d'))
        ->setTotalPay(round(((float)$salaryData[$i][7]),2)) // '7' = col H
        ->setPAYE(round(((float)$salaryData[$i][8]),2)) // '8' = col I
        ->setEmployeeNI(round(((float)$salaryData[$i][9]),2)) // '9' = col J
        ->setOtherDeductions(round(((float)$salaryData[$i][10]),2)) // '10' = col K
        ->setStudentLoan(round(((float)$salaryData[$i][12]),2)) // '12' = col M
        ->setNetPay(round(((float)$salaryData[$i][13]),2)) // '13' = col N
        ->setEmployerNI(round(((float)$salaryData[$i][14]),2)); // '14' = col O        

        $this->payslips[$payrollNumber] = $payslip;
    }    
    return true;
  }
  
  private function parsePensions(): bool {

    $worksheet = $this->pensionsWorkSheet;

    $rowindex=0;

    // First find the payment date that the file was prepared for
    foreach ($worksheet->getRowIterator() as $row) {
      $rowindex++;

      // ignore empty rows
      if(!$row->isEmpty()) { 

        // Looking for a string that looks like an employee Id in second column
        $value = $worksheet->getCell([2, $rowindex])->getValue();
        if ($value) {
          $value = trim($value);
          if (is_numeric($value)) {
            
            // Extract values from worksheet
            $employeeId = (int)$value;
            $employerPension = round(((float)$worksheet->getCell([8, $rowindex])
                ->getValue()),2);
            $employeePension = round(((float)$worksheet->getCell([9, $rowindex])
                ->getValue()),2);
            $salarySacrifice = round(((float)$worksheet->getCell([10, $rowindex])
                ->getValue()),2);


            // Found an employee Id, now try to find their payslip
            if (array_key_exists($employeeId, $this->payslips)) {
              // use found payslip
              $payslip = $this->payslips[$employeeId];

            } else {
              // Add new payslip
              $payslip = Payslip::getInstance()
              ->setPayrollNumber($employeeId);

              $this->payslips[$employeeId] = $payslip; // Add to list
            }

            // Adding new amounts to existing amounts because sometime employees
            // appear twice
            $payslip
              ->setEmployeePension($employeePension+$payslip->getEmployeePension())
              ->setEmployerPension($employerPension+$payslip->getEmployerPension())
              ->setSalarySacrifice($salarySacrifice+$payslip->getSalarySacrifice());
                
          }
            
        }
      }
      
    }   

    return true;
  }

}