/**
 * The name and id numbers of an employee
 */
export class EmployeeName {
  /**
   * The id of the Employee in Quickbooks. This will vary by realm.
   */
  quickbooksId: number;
  /**
   * Full name of the employee
   */
  name: string;
  /**
   * The number of the employee on the Iris printouts
   */
  payrollNumber: number;

  constructor(obj?: any) {
    this.quickbooksId = (obj && obj.quickbooksId) || null;
    this.name = (obj && obj.name) || null;
    this.payrollNumber = (obj && obj.payrollNumber) || null;
  }
}
