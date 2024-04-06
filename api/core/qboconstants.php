<?php

namespace Core;

use DateTime;

/**
 * A static class that holds application-level constants used in Quickbooks Online processes.
 * 
 * @category Core
 */
class QuickbooksConstants {

  const AUEW_ACCOUNT = "65";
  const EMPLOYEE_PENSION_CONTRIB_ACCOUNT = "66";
  const SALARY_SACRIFICE_ACCOUNT = "375";
  const NET_PAY_ACCOUNT = "98";
  const OTHER_DEDUCTIONS_ACCOUNT = "503";
  const PENSION_COSTS_ACCOUNT = "285";
  const TAX_ACCOUNT = "256";

  const ADMIN_CLASS = "1400000000000130710";

  const NOVAT_TAX_CODE = "20";

  const LEGAL_AND_GENERAL_VENDOR = "357";

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