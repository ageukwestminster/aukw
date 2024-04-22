import { IrisPayslip } from "@app/_models";

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
      xlsxPayslip.totalPay == quickbooksPayslip.totalPay &&
      xlsxPayslip.paye == quickbooksPayslip.paye &&
      xlsxPayslip.employeeNI == quickbooksPayslip.employeeNI &&
      xlsxPayslip.otherDeductions == quickbooksPayslip.otherDeductions &&
      xlsxPayslip.employeePension == quickbooksPayslip.employeePension &&
      xlsxPayslip.salarySacrifice == quickbooksPayslip.salarySacrifice &&
      xlsxPayslip.studentLoan == quickbooksPayslip.studentLoan &&
      xlsxPayslip.netPay == quickbooksPayslip.netPay
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
      xlsxPayslip.employerPension == 0 ||
      xlsxPayslip.employerPension == quickbooksPayslip.employerPension
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
      xlsxPayslip.employerNI == 0 ||
      xlsxPayslip.employerNI == quickbooksPayslip.employerNI
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
      (xlsxPayslip.totalPay == 0 &&
        xlsxPayslip.employerNI == 0 &&
        xlsxPayslip.employerPension == 0) ||
      (xlsxPayslip.totalPay == quickbooksPayslip.totalPay &&
        xlsxPayslip.employerNI == quickbooksPayslip.employerNI &&
        xlsxPayslip.employerPension == quickbooksPayslip.employerPension)
    );
  }