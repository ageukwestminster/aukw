/**
 * Stores a percentage allocation for a given employee (identified by payrollNumber, quickbooksId and name)
 * against a class / account pair. Also stores if the employee is a shop employee because this affects the
 * resulting QBO bookings.
 */
export class EmployeeAllocation {
  /** Database ID of the allocation record */
  id: number;
  /** Iris payroll number for employee */
  payrollNumber: number;
  /** QuickBooks employee id */
  quickbooksId: number;
  /** 'True' if the employee works in the shop */
  isShopEmployee: boolean = false;
  /** Display name of employee */
  name: string;
  /** Percentage of cost to be allocated to this account/class pair. Cannot be less than 0 or more than 100. */
  percentage: number;
  /** The account to allocate the cost to */
  account: number;
  /** The name of the account to allocate the cost to */
  accountName: string;
  /** The class to allocate the cost to */
  class: string;
  /** The name of the class to allocate the cost to */
  className: string;

  /**Create a new EmployeeAllocation */
  constructor(obj?: any) {
    this.id = (obj && obj.id) || null;
    this.payrollNumber = (obj && obj.payrollNumber) || null;
    this.quickbooksId = (obj && obj.quickbooksId) || 0;
    this.isShopEmployee = (obj && obj.isShopEmployee) || false;
    this.name = (obj && obj.name) || null;
    this.percentage = (obj && obj.percentage) || null;
    this.account = (obj && obj.account) || null;
    this.class = (obj && obj.class) || null;
    this.accountName = (obj && obj.accountName) || null;
    this.className = (obj && obj.className) || null;
  }
}
