import { IrisPayslip } from '@app/_models';

const TOLERANCE = 0.005; // 0.5 pence

/**
 * Compare the payslip calculated from the Iris spreadsheet with the payslip
 * calculated from QB values and see if they are equal, considering only the properties
 * that matter for calculating the journal entries needed for the charity QBO.
 * Return 'true' if they match
 * @param xlsxPayslip The payslip calculated from the Iris spreadsheet
 * @param quickbooksPayslip The payslip calculated from QB values
 * @returns 'true' if they are equal
 */
export function isEqualPay(
  xlsxPayslip: IrisPayslip,
  quickbooksPayslip: IrisPayslip,
): boolean {
  return (
    Math.abs(xlsxPayslip.totalPay - quickbooksPayslip.totalPay) < TOLERANCE &&
    Math.abs(xlsxPayslip.paye - quickbooksPayslip.paye) < TOLERANCE &&
    Math.abs(xlsxPayslip.employeeNI - quickbooksPayslip.employeeNI) <
      TOLERANCE &&
    Math.abs(xlsxPayslip.otherDeductions - quickbooksPayslip.otherDeductions) <
      TOLERANCE &&
    Math.abs(xlsxPayslip.employeePension - quickbooksPayslip.employeePension) <
      TOLERANCE &&
    Math.abs(xlsxPayslip.salarySacrifice - quickbooksPayslip.salarySacrifice) <
      TOLERANCE &&
    Math.abs(xlsxPayslip.studentLoan - quickbooksPayslip.studentLoan) <
      TOLERANCE &&
    Math.abs(xlsxPayslip.netPay - quickbooksPayslip.netPay) < TOLERANCE
  );
}

/**
 * Compare the payslip calculated from the Iris spreadsheet with the payslip
 * calculated from QB values and see if they are equal, considering only Employer pension.
 * Return 'true' if they match
 * @param xlsxPayslip The payslip calculated from the Iris spreadsheet
 * @param quickbooksPayslip The payslip calculated from QB values
 * @returns 'true' if they are equal
 */
export function isEqualPension(
  xlsxPayslip: IrisPayslip,
  quickbooksPayslip: IrisPayslip,
): boolean {
  return (
    Math.abs(xlsxPayslip.employerPension) < TOLERANCE ||
    Math.abs(xlsxPayslip.employerPension - quickbooksPayslip.employerPension) <
      TOLERANCE
  );
}

/**
 * Compare the payslip calculated from the Iris spreadsheet with the payslip
 * calculated from QB values and see if they are equal, considering only Employer NI.
 * Return 'true' if they match
 * @param xlsxPayslip The payslip calculated from the Iris spreadsheet
 * @param quickbooksPayslip The payslip calculated from QB values
 * @returns 'true' if they are equal
 */
export function isEqualEmployerNI(
  xlsxPayslip: IrisPayslip,
  quickbooksPayslip: IrisPayslip,
): boolean {
  return (
    Math.abs(xlsxPayslip.employerNI) < TOLERANCE ||
    Math.abs(xlsxPayslip.employerNI - quickbooksPayslip.employerNI) < TOLERANCE
  );
}

/**
 * Compare the payslip calculated from the Iris spreadsheet with the payslip
 * calculated from QB values and see if they are equal, considering only the properties
 * that matter for calculating the journal entries needed for the Enterprises QBO.
 * Return 'true' if they match
 * @param xlsxPayslip The payslip calculated from the Iris spreadsheet
 * @param quickbooksPayslip The payslip calculated from QB values
 * @returns 'true' if they are equal
 */
export function isEqualShopPay(
  xlsxPayslip: IrisPayslip,
  quickbooksPayslip: IrisPayslip,
): boolean {
  return (
    (Math.abs(xlsxPayslip.totalPay) < TOLERANCE &&
      Math.abs(xlsxPayslip.employerNI) < TOLERANCE &&
      Math.abs(xlsxPayslip.employerPension) < TOLERANCE) ||
    (Math.abs(xlsxPayslip.totalPay - quickbooksPayslip.totalPay) < TOLERANCE &&
      Math.abs(xlsxPayslip.employerNI - quickbooksPayslip.employerNI) <
        TOLERANCE &&
      Math.abs(
        xlsxPayslip.employerPension - quickbooksPayslip.employerPension,
      ) < TOLERANCE)
  );
}
