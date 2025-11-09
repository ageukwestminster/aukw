import { PayrollIdentifier } from '@app/_interfaces/payroll-identifier';

/**
 * Stores the detail of a QBO entity 'Line'
 */
export class LineItemDetail implements PayrollIdentifier {
  /** Iris payroll number for employee */
  payrollNumber: number;
  /** QuickBooks employee id */
  quickbooksId: number;
  /** 'True' if the employee works in the shop */
  isShopEmployee: boolean = false;
  /** Display name of employee */
  name: string;
  /** Amount of employer NI allocated to this account/class pair. */
  amount: number;
  /** The account to allocate the cost to */
  account: number;
  /** The name of the account to allocate the cost to */
  accountName: string;
  /** The class to allocate the cost to */
  class: string;
  /** The name of the class to allocate the cost to */
  className: string;
  /** 
   * Add the values from a specified LineItemDetail to this instance
   * @param IrisPayslip The LineItemDetail to add to this instance.
   * @returns This instance
   */
  add(obj?: any) : LineItemDetail {
    this.amount += (obj && obj.amount) || 0;
    return this;
  }

  constructor(obj?: any) {
    this.quickbooksId = (obj && obj.quickbooksId) || 0;
    this.name = (obj && obj.name) || null;
    this.amount = (obj && obj.amount) || 0;
    this.account = (obj && obj.account) || null;
    this.class = (obj && obj.class) || null;
    this.payrollNumber = (obj && obj.payrollNumber) || null;
    this.accountName = (obj && obj.accountName) || null;
    this.className = (obj && obj.className) || null;
    this.isShopEmployee = obj && obj.isShopEmployee;
  }
}
