import { IrisPayslip } from '@app/_models';

const TOLERANCE = 0.005; // 0.5 pence

/**
 * Compare the payslip calculated from the Iris spreadsheet with the payslip
 * calculated from QB values and see if they are equal, considering only the properties
 * that matter for calculating the journal entries needed for the charity QBO.
 * Return 'true' if they match
 * @param irisPayslip The payslip obtained from Iris/FMP, our payroll provider
 * @param quickbooksPayslip The payslip calculated from QB values
 * @returns 'true' if they are equal
 */
export function isEqualPay(
  irisPayslip: IrisPayslip,
  quickbooksPayslip: IrisPayslip,
): boolean {
  return (
    Math.abs(irisPayslip.totalPay - quickbooksPayslip.totalPay) < TOLERANCE &&
    Math.abs(irisPayslip.paye - quickbooksPayslip.paye) < TOLERANCE &&
    Math.abs(irisPayslip.employeeNI - quickbooksPayslip.employeeNI) <
      TOLERANCE &&
    Math.abs(irisPayslip.otherDeductions - quickbooksPayslip.otherDeductions) <
      TOLERANCE &&
    Math.abs(irisPayslip.employeePension - quickbooksPayslip.employeePension) <
      TOLERANCE &&
    Math.abs(irisPayslip.salarySacrifice - quickbooksPayslip.salarySacrifice) <
      TOLERANCE &&
    Math.abs(irisPayslip.studentLoan - quickbooksPayslip.studentLoan) <
      TOLERANCE &&
    Math.abs(irisPayslip.netPay - quickbooksPayslip.netPay) < TOLERANCE
  );
}

/**
 * Compare the payslip calculated from the Iris spreadsheet with the payslip
 * calculated from QB values and see if they are equal, considering only Employer pension.
 * Return 'true' if they match
 * @param irisPayslip The payslip obtained from Iris/FMP, our payroll provider
 * @param quickbooksPayslip The payslip calculated from QB values
 * @returns 'true' if they are equal
 */
export function isEqualPension(
  irisPayslip: IrisPayslip,
  quickbooksPayslip: IrisPayslip,
): boolean {
  return (
    Math.abs(irisPayslip.employerPension) < TOLERANCE ||
    Math.abs(irisPayslip.employerPension - quickbooksPayslip.employerPension) <
      TOLERANCE
  );
}

/**
 * Compare the payslip calculated from the Iris spreadsheet with the payslip
 * calculated from QB values and see if they are equal, considering only Employer NI.
 * Return 'true' if they match
 * @param irisPayslip The payslip obtained from Iris/FMP, our payroll provider
 * @param quickbooksPayslip The payslip calculated from QB values
 * @returns 'true' if they are equal
 */
export function isEqualEmployerNI(
  irisPayslip: IrisPayslip,
  quickbooksPayslip: IrisPayslip,
): boolean {
  return (
    Math.abs(irisPayslip.employerNI) < TOLERANCE ||
    Math.abs(irisPayslip.employerNI - quickbooksPayslip.employerNI) < TOLERANCE
  );
}

/**
 * Compare the payslip calculated from the Iris spreadsheet with the payslip
 * calculated from QB values and see if they are equal, considering only the properties
 * that matter for calculating the journal entries needed for the Enterprises QBO.
 * Return 'true' if they match
 * @param irisPayslip The payslip obtained from Iris/FMP, our payroll provider
 * @param quickbooksPayslip The payslip calculated from QB values
 * @returns 'true' if they are equal
 */
export function isEqualShopPay(
  irisPayslip: IrisPayslip,
  quickbooksPayslip: IrisPayslip,
): boolean {
  return (
    (Math.abs(irisPayslip.totalPay) < TOLERANCE &&
      Math.abs(irisPayslip.employerNI) < TOLERANCE &&
      Math.abs(irisPayslip.employerPension) < TOLERANCE) ||
    (Math.abs(irisPayslip.totalPay - quickbooksPayslip.totalPay) < TOLERANCE &&
      Math.abs(irisPayslip.employerNI - quickbooksPayslip.employerNI) <
        TOLERANCE &&
      Math.abs(
        irisPayslip.employerPension - quickbooksPayslip.employerPension,
      ) < TOLERANCE)
  );
}
