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

import { Allocation } from './allocation'

export class PayrollJournalEntry {
  employeeId: number;
  totalPay: Allocation[];
  paye: number;
  employeeNI: number;
  otherDeductions: number;
  salarySacrifice: number;
  employeePension: number;
  studentLoan: number;
  netPay: number;
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
