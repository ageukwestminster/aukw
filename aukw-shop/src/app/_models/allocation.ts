/**
 * Stores a percentage allocation for a given employee (identified by payrollNumber, quickbooksId and name)
 * against a class / account pair. Also stores if the employee is a shop employee because this affects the
 * resulting QBO bookings.
 */
export class EmployeeAllocation {
  /** Iris payroll number for employee */
  payrollNumber: number;
  /** Quickbooks employee id */
  quickbooksId: number;
  /** 'True' if the employee works in the shop */
  isShopEmployee: boolean = false;
  /** Display name of employee */
  name: string;
  /** Percentage of cost to be allocated to this account/class pair. Cannot be less than 0 or more than 100. */
  percentage: number;
  /** The account to allocate the cost to */
  account: number;
  /** The class to allocate the cost to */
  class: string;

  /**Create a new EmployeeAllocation */
  constructor(obj?: any) {
    this.payrollNumber = (obj && obj.payrollNumber) || null;
    this.quickbooksId = (obj && obj.quickbooksId) || 0;
    this.isShopEmployee = (obj && obj.isShopEmployee) || false;
    this.name = (obj && obj.name) || null;
    this.percentage = (obj && obj.percentage) || null;
    this.account = (obj && obj.account) || null;
    this.class = (obj && obj.class) || null;
  }
}
