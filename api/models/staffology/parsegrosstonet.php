<?php
namespace Models\Staffology;

use DateTime;
use Exception;

use Models\Payslip;

/**
 * A static class to provide functionality to parse payroll data from Staffology API
 * and turn it into a list of Payslips.
 * 
 * @category Model
 */
abstract class ParseGrosstoNetReport{

  public static function parse(array $salaryData, string $payrollDate = ''): array {

    if ($payrollDate != '') {
      if (DateTime::createFromFormat('Y-m-d', $payrollDate) !== false) {
        $paymentDate = DateTime::createFromFormat('Y-m-d', $payrollDate);
      } else if (DateTime::createFromFormat('d/m/Y', $payrollDate) !== false) {
        $paymentDate = DateTime::createFromFormat('d/m/y', $payrollDate);
      } else {
        throw new Exception('Unable to set date from supplied http parameter value: "'. $payrollDate . '." .
          " Try entering the date in the format day/month/year or day-month-year.');
      }  
    } else {
      $paymentDate = DateTime::createFromFormat('Y-m-d', date('Y-m-d'));
    }

    // Loop through employees, creating payslips
    $payslips = array();
    foreach($salaryData as $salaryRow) {
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
      $salarySacrifice = ParseGrosstoNetReport::calculateSalarySacrifice(
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
        ->setPayrollDate($paymentDate->format('Y-m-d'))
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
  private static function calculateSalarySacrifice(
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