
/**
 * For a given employee, identified by payrollNumber, store whether their salary amounts are recorded in
 * Quickbooks. Four different flags are used to cover all the dimensions of their pay.
 */
export class QBTransactionFlags {
  /** Iris payroll number for employee */
  payrollNumber: number;
  /** 'True' if the amount of Employer NI for this employee has been recorded in Quickbooks. */
  employerNI: boolean;
  /** 'True' if the Employer pension amount for this employee has been recorded in Quickbooks. */
  pensionBill: boolean;
  /** 'True' if the employee's salary and deductions have been booked in QBO.  */
  employeeJournal: boolean;
  /** 'True' if the shop employee's salary, NI and pension has been booked in the Enterpises QBO company.  */
  shopJournal: boolean | null;

  constructor(obj?: any) {
    this.payrollNumber = (obj && obj.payrollNumber) || null;
    this.employerNI = (obj && obj.issandbox);
    this.pensionBill = (obj && obj.pensionBill);
    this.employeeJournal = (obj && obj.employeeJournal);
    this.shopJournal = (obj && obj.shopJournal);
  }
}
