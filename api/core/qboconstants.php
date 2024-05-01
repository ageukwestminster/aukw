<?php

namespace Core;

use DateTime;

/**
 * A static class that holds application-level constants used in Quickbooks Online processes.
 * 
 * @category Core
 */
class QuickbooksConstants {  

  // Charity Constants
  const CHARITY_REALMID = "123145825016867";

  // if any account number clashes with AUEW account numbers then check parsePayrollJournals() carefully
  const AUEW_ACCOUNT = "65";
  const EMPLOYEE_PENSION_CONTRIB_ACCOUNT = "66";
  const EMPLOYER_NI_ACCOUNT = "95";
  const SALARY_SACRIFICE_ACCOUNT = "375";
  const NET_PAY_ACCOUNT = "98";
  const OTHER_DEDUCTIONS_ACCOUNT = "503";
  const PENSION_COSTS_ACCOUNT = "285";
  const STAFF_SALARIES_ACCOUNT = "261";
  const TAX_ACCOUNT = "256";

  const ADMIN_CLASS = "1400000000000130710";

  const EMPLOYEE_NI_DESCRIPTION = "Employee NI";
  const EMPLOYER_NI_DESCRIPTION = "Employer NI";
  const EMPLOYEE_PENSION_CONT_DESCRIPTION = "Employee Pension Contribution";
  const EMPLOYER_PENSION_CONT_DESCRIPTION = "Employer Pension Contribution";
  const GROSS_SALARY_DESCRIPTION = "Gross Salary";
  const NET_PAY_DESCRIPTION = "Net Pay";
  const OTHER_DEDUCTIONS_DESCRIPTION = "Other Deductions";
  const PAYE_DESCRIPTION = "PAYE";
  const SALARY_SACRIFICE_DESCRIPTION = "Salary Sacrifice";
  const STUDENT_LOAN_DESCRIPTION = "Student Loan Deductions";

  const NOVAT_TAX_CODE = "20";

  const LEGAL_AND_GENERAL_VENDOR = "357";

  // Enterprises Constants
  const ENTERPRISES_REALMID = "9130350604308576";
  
  const AUKW_INTERCO_ACCOUNT = "80";
  const AUEW_SALARIES_ACCOUNT = "106";
  const AUEW_NI_ACCOUNT = "150";
  const AUEW_PENSIONS_ACCOUNT = "139";

  const HARROW_ROAD_CLASS = "400000000000618070";

  /**
   * Helper function to regularise the DocNumber for payroll transactions
   * @param string $payrollDate A string representation of the date of the 
   * payroll in 'YYYY-mm-dd' format.
   * @return string 
   */
  public static function payrollDocNumber(string $payrollDate) : string {
    $d = DateTime::createFromFormat('Y-m-d', $payrollDate);
    return 'Payroll_' . $d->format('Y_m');
  }
}