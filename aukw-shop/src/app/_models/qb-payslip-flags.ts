/**
 * For a given employee, identified by payrollNumber, store whether their salary amounts are recorded in
 * Quickbooks. Four different flags are used to cover all the dimensions of their pay.
 */
export class QBTransactionFlags {
  /** 'True' if the amount of Employer NI for this employee has been recorded in Quickbooks. */
  employerNI: boolean;
  /** 'True' if the Employer pension amount for this employee has been recorded in Quickbooks. */
  pensionBill: boolean ;
  /** 'True' if the employee's salary and deductions have been booked in QBO.  */
  employeeJournal: boolean;
  /** 'True' if the shop employee's salary, NI and pension has been booked in the Enterprises QBO company.  */
  shopJournal: boolean;

  constructor(obj?: any) {
    this.employerNI = obj && obj.employerNI;
    this.pensionBill = obj && obj.pensionBill;
    this.employeeJournal = obj && obj.employeeJournal;
    this.shopJournal = obj && obj.shopJournal;
  }
}
