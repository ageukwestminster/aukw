/**
 * Define the properties of an employee allocation
 */

export class EmployeeAllocation {
  id: number;
  name: string;
  percentage: number;
  account: number;
  class: string;
  payrollNumber: number;

  constructor(obj?: any) {
    this.id = (obj && obj.id) || 0;
    this.name = (obj && obj.name) || null;
    this.percentage = (obj && obj.percentage) || null;
    this.account = (obj && obj.account) || null;
    this.class = (obj && obj.class) || null;
    this.payrollNumber = (obj && obj.payrollNumber) || null;
  }
}

/**
 * Define the properties of an employee allocation
 */

export class PensionAllocation {
  id: number;
  name: string;
  amount: number;
  account: number;
  class: string;

  constructor(obj?: any) {
    this.id = (obj && obj.id) || null;
    this.name = (obj && obj.name) || null;
    this.amount = (obj && obj.amount) || null;
    this.account = (obj && obj.account) || null;
    this.class = (obj && obj.class) || null;
  }
}
