export class EmployerNIEntry {
  employeeId: number;
  amount: number;
  account: number;
  class: number;

  constructor(obj?: any) {
    this.employeeId = (obj && obj.employeeId) || null;
    this.amount = (obj && obj.amount) || null;
    this.account = (obj && obj.account) || null;
    this.class = (obj && obj.class) || null;
  }
}

import { LineItemDetail } from './line-item-detail';

export class PayrollJournalEntry {
  /** Quickbooks employee id */
  employeeId: number;
  /** Gross monthly salary, split into allocated amounts */
  totalPay: LineItemDetail[];
  /** Income tax deduction */
  paye: number;
  /** National insurance deduction paid by the employee */
  employeeNI: number;
  /** Salary reduction taken by employee and paid into pension pot */
  salarySacrifice: number;
  /** Deduction taken by government to pay for university education */
  studentLoan: number;
  /** Catch all for all other deductions */
  otherDeductions: number;
  /** Pension contribution paid by employee, if not salary sacrifice */
  employeePension: number;
  /** Net monthly salary, the amount paid to the employee. */
  netPay: number;
  /** Display name of employee */
  name: string;

  constructor(obj?: any) {
    this.employeeId = (obj && obj.employeeId) || null;
    this.name = (obj && obj.name) || null;
    this.paye = (obj && obj.paye) || 0;
    this.employeeNI = (obj && obj.employeeNI) || 0;
    this.otherDeductions = (obj && obj.otherDeductions) || 0;
    this.totalPay = (obj && obj.totalPay) || [];
    this.salarySacrifice = (obj && obj.salarySacrifice) || 0;
    this.employeePension = (obj && obj.employeePension) || 0;
    this.studentLoan = (obj && obj.studentLoan) || 0;
    this.netPay = (obj && obj.netPay) || 0;
  }
}
