<?php

namespace Controllers;

use \Core\QuickbooksConstants as QBO;
use \Models\Payslip;
use \Models\QuickbooksQuery;
use \Models\QuickbooksEmployee;

/**
 * Controller to query QBO and return (in Payslip format) details of current month's payroll
 *
 * @category  Controller
*/
class QBPayrollQueryCtl{

  /**
   * Query QBO and return (in Payslip format) details of current month's payroll
   *
   * @param string $realmid The company ID for the QBO company.
   * @param int $year 
   * @param int $month
   * @return void Output is echo'd directly to response 
   */
  public static function query(string $realmid, int $year, int $month){  

    $payslips = array(); // this will be returned

    $payrollIdentifier = QBO::payrollDocNumber($year . '-' . $month . '-25'); // day of month is irrelevent, using 25

    $employees = QuickbooksEmployee::getInstance()
      ->setRealmID($realmid)
      ->readAllAssociatedByName();

    $bills = QuickbooksQuery::getInstance()
      ->setRealmID($realmid)
      ->query_by_docnumber($payrollIdentifier, 'Bill');

    QBPayrollQueryCtl::parsePensionBills($employees, $bills, $payslips);
    
    $journals = QuickbooksQuery::getInstance()
      ->setRealmID($realmid)
      ->query_by_docnumber($payrollIdentifier, 'JournalEntry');

    QBPayrollQueryCtl::parsePayrollJournals($employees, $journals, $payslips);

    echo json_encode(array_values($payslips));
  }

  /**
   * Run through the payroll journals and extract details of the month's salary and deeduction amounts
   * 
   * @return void 
   */
  private static function parsePayrollJournals(Array $employees, Array $journals, &$payslips):void {

    // Convert $employees from keyed on employee name to keyed on QB id.
    $employeesById = array();
    foreach($employees as $employee) {
      $employeesById[$employee['quickbooksId']] = $employee;
    }

    foreach($journals as $journal) {
      // Check that $journal is of type IPPTransaction
      if (property_exists($journal, 'Line') && is_array($journal->Line)) {
        
        foreach($journal->Line as $line) {
          $detail = $line->JournalEntryLineDetail;          
          
          if (!isset($detail->Entity) || !property_exists($detail->Entity, 'Type') 
            || $detail->Entity->Type != 'Employee' || !isset($detail->Entity->EntityRef)) {
            break;
          }

          if ( property_exists($detail->Entity->EntityRef, 'value') ) {
            $employeeId = $detail->Entity->EntityRef->value;
          } else {
            $employeeId = $detail->Entity->EntityRef;
          }
          $payrollNumber = $employeesById[$employeeId]['payrollNumber'];

          if (!array_key_exists($payrollNumber, $payslips)) {
              // Create a new payslip if none found
              $payslips[$payrollNumber] = QBPayrollQueryCtl::createPayslip(
                $payrollNumber,
                $employeeId,
                $employeesById[$employeeId]['name']
              );   
          }

          $payslip = $payslips[$payrollNumber];

          $amount = $line->Amount;

          switch ($detail->AccountRef) {
            case QBO::AUEW_ACCOUNT:
              switch ($line->Description) {
                case QBO::EMPLOYER_NI_DESCRIPTION:
                  $payslip->addToEmployerNI($amount);
                  break;
                case QBO::GROSS_SALARY_DESCRIPTION:
                  $payslip->addToTotalPay($amount);
                  break;
                default:
              }
              break;
            case QBO::AUEW_SALARIES_ACCOUNT:
            case QBO::STAFF_SALARIES_ACCOUNT:
              $payslip->addToTotalPay($amount);
              break;
            case QBO::TAX_ACCOUNT:
              switch ($line->Description) {
                case QBO::PAYE_DESCRIPTION:
                  if($detail->PostingType == "Credit") {
                    $amount *= -1;
                  }
                  $payslip->addToPAYE($amount);
                  break;
                case QBO::EMPLOYEE_NI_DESCRIPTION:
                  if($detail->PostingType == "Credit") {
                    $amount *= -1;
                  }
                  $payslip->addToEmployeeNI($amount);
                  break;
                case QBO::STUDENT_LOAN_DESCRIPTION:
                  if($detail->PostingType == "Credit") {
                    $amount *= -1;
                  }
                  $payslip->addToStudentLoan($amount); 
                  break;
                default:
              }
              break;
            case QBO::OTHER_DEDUCTIONS_ACCOUNT:
              if($detail->PostingType == "Credit") {
                $amount *= -1;
              }
              $payslip->addToOtherDeductions($amount); 
              break;
            case QBO::SALARY_SACRIFICE_ACCOUNT:
              $payslip->addToSalarySacrifice($amount); 
              break;
            case QBO::EMPLOYEE_PENSION_CONTRIB_ACCOUNT:
              $payslip->addToEmployeePension($amount); 
              break;
            case QBO::NET_PAY_ACCOUNT:    
              $payslip->addToNetPay($amount);            
              break;
            case QBO::AUEW_NI_ACCOUNT:
            case QBO::EMPLOYER_NI_ACCOUNT:
              $payslip->addToEmployerNI($amount);
              break;
            case QBO::AUEW_PENSIONS_ACCOUNT:
              $payslip->addToEmployerPension($amount);
              break;
            case QBO::AUKW_INTERCO_ACCOUNT:
              break;
            default:
              break;
          }
        }
      }
    }
  }

    /**
   * Run through the pension bills and extract details of the month's pension amounts
   * 
   * @return void 
   */
  private static function parsePensionBills (Array $employees, Array $bills, &$payslips):void {
    foreach($bills as $bill) {
      // $bill is of type QuickBooksOnline\API\Data\IPPBill with extends IPPTransaction
      if (property_exists($bill, 'Line') && is_array($bill->Line)) {

        foreach($bill->Line as $line) {
          
          // It's expected that the Employee name is in Description/Memo 
          // if bill created by this system then it will be
          $name = $line->Description; 
          
          // Is the employee found in the QBO list of employees?
          if (array_key_exists($name, $employees)) {

            // Determine their iris Payroll number
            $payrollNumber = $employees[$name]['payrollNumber'];
            
            if (!array_key_exists($payrollNumber, $payslips)) {

              // Create a new payslip
              $payslips[$payrollNumber] = QBPayrollQueryCtl::createPayslip(
                  $payrollNumber, 
                  $employees[$name]['quickbooksId'], 
                  $name
                )->setEmployerPension($line->Amount);   

            } else {              
              $payslips[$payrollNumber]->addToEmployerPension($line->Amount); 
            }
          } 
        }
      }
    }
  }

  private static function createPayslip($payrollNumber, $quickbooksId, $name) : Payslip {
    return Payslip::getInstance()
    ->setEmployeeId($payrollNumber)
    ->setQuickbooksId($quickbooksId)
    ->setEmployeeName($name);
  }
}