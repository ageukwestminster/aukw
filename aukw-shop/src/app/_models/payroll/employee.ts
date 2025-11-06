/**
 * The name and id numbers of an employee
 */
export class EmployeeName {
  /**
   * The id of the Employee in QuickBooks. This will vary by realm.
   */
  quickbooksId: number;
  /**
   * Full name of the employee
   */
  name: string;
  /**
   * Given name of the employee
   */
  firstName: string;
  /**
   * Family name of the employee
   */
  lastName: string;
  /**
   * Middle name of the employee
   */
  middleName: string;
  /**
   * The number of the employee on the Iris printouts
   */
  payrollNumber: number;

  constructor(obj?: any) {
    this.quickbooksId = (obj && obj.quickbooksId) || null;
    this.name = (obj && obj.name) || null;
    this.payrollNumber = (obj && obj.payrollNumber) || null;
    this.firstName = (obj && obj.name) || null;
    this.lastName = (obj && obj.name) || null;
    this.middleName = (obj && obj.name) || null;
  }
}
