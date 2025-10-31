<?php
namespace Models\Staffology;

use DateTime;
use Exception;

use Core\Config;
use Models\Payslip;
use Services\PayrollApiService;
/**
 * 
 * 
 * @category Model
 */
class GrossToNetReport{
  /**
  * Service object to call Payroll API
  * @var PayrollApiService|null
  */ 
  private $apiService;
  /**
   * The Staffology Employer Id
   *
   * @var string
   */
  protected string $employerId;
    /**
   * The Staffology tax year. It's a string like 'Year2025', 'Year2024', etc.
   *
   * @var string
   */
  protected string $taxYear;

  /**
   * The from period
   *
   * @var int
   */
  protected int $fromPeriod;
  /**
   * The to period
   *
   * @var int
   */
  protected int $toPeriod;
  /**
   * The sort order, done on Payroll Code (i.e. employee number)
   *
   * @var bool
   */
  protected bool $sortDescending;
  /**
   * The date that the salaries will be paid to the employees.
   *
   * @var DateTime
   */
  protected DateTime $paymentDate;

    /**
   * The information about salaries retrieved from Staffology API
   *
   * @var array
   */
  protected array $salaryData;

  /**
   * paymentDate setter
   */
  public function setPaymentDate(string $paymentDate) {
    $this->paymentDate = DateTime::createFromFormat('Y-m-d', $paymentDate);
    return $this;
  }

  /**
   * paymentDate getter.
   */
  public function getPaymentDate() : DateTime {
    return $this->paymentDate;
  } 

    /**
   * sortDescending setter
   */
  public function setSortDescending(bool $sortDescending) {
    $this->sortDescending = $sortDescending;
    return $this;
  }

  /**
   * sortDescending getter.
   */
  public function getSortDescending() : bool {
    return $this->sortDescending;
  } 

  /**
   * fromPeriod setter
   */
  public function setFromPeriod(int $fromPeriod) {
    $this->fromPeriod = $fromPeriod;
    return $this;
  }

  /**
   * fromPeriod getter.
   */
  public function getFromPeriod() : int {
    return $this->fromPeriod;
  } 

  /**
   * toPeriod setter
   */
  public function setToPeriod(int $toPeriod) {
    $this->toPeriod = $toPeriod;
    return $this;
  }

  /**
   * toPeriod getter.
   */
  public function getToPeriod() : int {
    return $this->toPeriod;
  } 

  /**
   * Employer Id setter
   */
  public function setEmployerId(string $employerId) {
    $this->employerId = $employerId;
    return $this;
  }

  /**
   * Employer ID getter.
   */
  public function getEmployerId() : string {
    return $this->employerId;
  } 

   /**
   * Employer Id setter
   */
  public function setTaxYear(string $taxYear) {
    $this->taxYear = $taxYear;
    return $this;
  }

  /**
   * Employer ID getter.
   */
  public function getTaxYear() : string {
    return $this->taxYear;
  }  
  /**
   * Constructor
   */
  protected function __construct(){
    $this->apiService = new PayrollApiService();
  }

  /**
   * Static constructor / factory
   */
  public static function getInstance() {    
    return new self();
  }

  /**
   * Go to the external payroll api and download the Gross-To-Net report for the given year and month.
   */
  public function read(){

    // Build endpoint
    $endpoint = 'employers/' . $this->employerId 
                  . '/reports/'
                  . $this->taxYear
                  . '/'
                  . Config::read('staffology.payperiod') 
                  . '/gross-to-net';

    $params = array(
      'fromPeriod' => $this->fromPeriod,
      'toPeriod' => $this->toPeriod,
      'sortDescending' => $this->sortDescending ? 'true' : 'false'
    );

    $report = $this->apiService->get($endpoint, $params);
    
    $this->salaryData=array();

    if (isset($report['model']) &&isset($report['model']['lines']) && is_array($report['model']['lines'])) {
      foreach ($report['model']['lines'] as $payslip) {
        $this->salaryData[] = $payslip;        
      }
    } else {
      throw new \Exception("Unexpected response from Staffology API when retrieving GrossToNet. Not an Array.");
    }
    
    return $this;
    
  }
        
    /**
   * Parse the Gross to Net worksheet, creating payslips for each employee.
   * @param string $payrollDate 
   * @return bool 
   * @throws Exception 
   */
  public function parse(string $payrollDate = ''): array {

    if ($payrollDate != '') {
      if (DateTime::createFromFormat('Y-m-d', $payrollDate) !== false) {
        $this->paymentDate = DateTime::createFromFormat('Y-m-d', $payrollDate);
      } else if (DateTime::createFromFormat('d/m/Y', $payrollDate) !== false) {
        $this->paymentDate = DateTime::createFromFormat('d/m/y', $payrollDate);
      } else {
        throw new Exception('Unable to set date from supplied http parameter value: "'. $payrollDate . '." .
          " Try entering the date in the format day/month/year or day-month-year.');
      }  
    } else {
      $this->paymentDate = DateTime::createFromFormat('Y-m-d', date('Y-m-d'));
    }

    // Loop through employees, creating payslips
    $payslips = array();
    foreach($this->salaryData as $salaryRow) {
      $payrollNumber = (int) trim($salaryRow['payrollCode']); // '0' = column A

      // We are always rounding the numbers to 2 decimal places to avoid floating point precision issues
      $totalPay = round((float) trim($salaryRow['totalGross']),2);
      $netPay = round((float) trim($salaryRow['netPay']),2);
      $paye = round((float) trim($salaryRow['tax']),2);
      $employeeNI = round((float) trim($salaryRow['employeeNi']),2);
      $employerNI = round((float) trim($salaryRow['employerNi']),2);
      $employeePension = round((float) trim($salaryRow['employeePension']),2);
      $employerPension = round((float) trim($salaryRow['employerPension']),2);
      $studentLoan = round((float) trim($salaryRow['studentOrPgLoan']),2);
      $statutoryPayments = round((float) trim($salaryRow['statutoryPayments']),2); // e.g. SSP, SMP
      $attachments = round((float) trim($salaryRow['attachments']),2); // e.g. court orders
      $otherDeductions = round((float) trim($salaryRow['otherDeductions']),2);

      // Calculate Salary Sacrifice by determining how net pay compares to the expected amount.
      $salarySacrifice = $this->calculateSalarySacrifice(
        $totalPay,
        $employeePension,
        $netPay,
        $paye,
        $employeeNI,
        $studentLoan,
        $attachments,
        $statutoryPayments,
        $otherDeductions
      );
      
      // the employee pension variable is only for genuine out-of-pay contributions, not salary sacrifice
      // so reduce it by the salary sacrifice amount.
      $employeePension -= $salarySacrifice;

      $payslip = Payslip::getInstance()
        ->setPayrollNumber($payrollNumber) 
        ->setEmployeeName(trim($salaryRow['employee']['name'])) // '1' = column B
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


        $payslips[] = $payslip;
    }    
    return $payslips;
  }
 
  /**
   * Calculates the salary sacrifice for an employee.
   *
   * @param float $totalPay
   * @param float $employeePension
   * @param float $netPay
   * @param float $paye
   * @param float $employeeNI
   * @param float $studentLoan
   * @param float $attachments
   * @param float $statutoryPayments
   * @param float $otherDeductions
   * @return float
   */
  private function calculateSalarySacrifice(
    float $totalPay,
    float $employeePension,
    float $netPay,
    float $paye,
    float $employeeNI,
    float $studentLoan,
    float $attachments,
    float $statutoryPayments,
    float $otherDeductions
  ): float {
    return round(
      ($totalPay + $employeePension) -
      (
        $netPay +
        $paye +
        $employeeNI +
        $studentLoan +
        $attachments +
        $statutoryPayments +
        $otherDeductions
      ),
      2
    );
  }
}