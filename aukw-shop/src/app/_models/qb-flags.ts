/**
 * Defines the properties of a containing booolean flags that record
 * whether a
 */
export class QBFlags {
  /**
   * Quickbooks employee id
   */
  id: number;
  /**
   * Iris employee id
   */
  payrollNumber: number;
  /**
   * 'True' if employee works in the shop
   */
  isShopEmployee: boolean = false;
  /** 'True' if the employee's Employer NI has been booked in QBO.  */
  niJournalInQBO: boolean = false;
  /** 'True' if the employee's Employer pension contribution has been booked in QBO.  */
  pensionBillInQBO: boolean = false;
  /** 'True' if the employee's salary and deductions have been booked in QBO.  */
  payslipJournalInQBO: boolean = false;
  /** 'True' if the shop employee's salary, NI and pension has been booked in the Enterrpises QBO company.  */
  shopJournalInQBO: boolean = false;

  constructor(obj?: any) {
    this.id = (obj && obj.id) || null;
    this.payrollNumber = (obj && obj.payrollNumber) || null;
    this.isShopEmployee = (obj && obj.isShopEmployee) || false;
    this.niJournalInQBO = (obj && obj.niJournalInQBO) || false;
    this.pensionBillInQBO = (obj && obj.pensionBillInQBO) || false;
    this.payslipJournalInQBO = (obj && obj.payslipJournalInQBO) || false;
    this.shopJournalInQBO = (obj && obj.shopJournalInQBO) || false;
  }
}
