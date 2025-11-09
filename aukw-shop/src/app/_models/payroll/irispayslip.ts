/**
 * Define the properties of an employee payslip
 */

import { QBTransactionFlags } from '@app/_models';

/*
    {
        "payrollNumber": 7,
        "employeeName": "Mehfuz Ahmed",
        "totalPay": 4420,
        "paye": -621.2,
        "employeeNI": -372.82,
        "otherDeductions": 0,
        "salarySacrifice": 265.2,
        "studentLoan": 0,
        "netPay": 3160.78,
        "employerNI": 468.76,
        "employerPension": 397.8,
        "employeePension": 0
    },
    */
export class IrisPayslip {
  /** Iris payroll number for employee */
  payrollNumber: number;
  /** QuickBooks employee id */
  quickbooksId: number;
  /** Display name of employee */
  employeeName: string;
  /** Date of payroll run in string format */
  payrollDate: string;
  /** Gross monthly salary */
  totalPay: number;
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
  /** Net monthly salary, the amount paid to the employee. */
  netPay: number;
  /** National Insurance paid by the employer */
  employerNI: number;
  /** Pension contribution paid by employer */
  employerPension: number;
  /** Pension contribution paid by employee, if not salary sacrifice */
  employeePension: number;
  /** 'True' if employee works in the shop */
  isShopEmployee: boolean = false;
  /** 'True' if the employee's Employer NI has been booked in QBO.  */
  niJournalInQBO: boolean = false;
  /** 'True' if the employee's Employer pension contribution has been booked in QBO.  */
  pensionBillInQBO: boolean = false;
  /** 'True' if the employee's salary and deductions have been booked in QBO.  */
  payslipJournalInQBO: boolean = false;
  /** 'True' if the shop employee's salary, NI and pension has been booked in the Enterprises QBO company. */
  shopJournalInQBO: boolean = false;
  /** QuickBooks transaction flags for this employee */
  //qbFlags: QBTransactionFlags;
  /** 'True' if the employee is missing from QuickBooks */
  employeeMissingFromQBO: boolean = false;
  /** 'True' if the project allocations for this employee are missing from QuickBooks */
  allocationsMissingFromQBO: boolean = false;

  add(obj: IrisPayslip): IrisPayslip {
    this.totalPay += (obj && obj.totalPay) || 0;
    this.paye += (obj && obj.paye) || 0;
    this.employeeNI += (obj && obj.employeeNI) || 0;
    this.otherDeductions += (obj && obj.otherDeductions) || 0;
    this.salarySacrifice += (obj && obj.salarySacrifice) || 0;
    this.studentLoan += (obj && obj.studentLoan) || 0;
    this.netPay += (obj && obj.netPay) || 0;
    this.employerNI += (obj && obj.employerNI) || 0;
    this.employerPension += (obj && obj.employerPension) || 0;
    this.employeePension += (obj && obj.employeePension) || 0;
    return this;
  }

  constructor(obj?: any) {
    this.payrollNumber = (obj && obj.payrollNumber) || null;
    this.quickbooksId = (obj && obj.quickbooksId) || null;
    this.employeeName = (obj && obj.employeeName) || null;
    this.payrollDate = (obj && obj.payrollDate) || null;
    this.totalPay = (obj && obj.totalPay) || 0;
    this.paye = (obj && obj.paye) || 0;
    this.employeeNI = (obj && obj.employeeNI) || 0;
    this.otherDeductions = (obj && obj.otherDeductions) || 0;
    this.salarySacrifice = (obj && obj.salarySacrifice) || 0;
    this.studentLoan = (obj && obj.studentLoan) || 0;
    this.netPay = (obj && obj.netPay) || 0;
    this.employerNI = (obj && obj.employerNI) || 0;
    this.employerPension = (obj && obj.employerPension) || 0;
    this.employeePension = (obj && obj.employeePension) || 0;
    this.isShopEmployee = obj && obj.isShopEmployee;
    //this.qbFlags = (obj && obj.qbFlags) || null;
    this.employeeMissingFromQBO = obj && obj.employeeMissingFromQBO;
    this.allocationsMissingFromQBO = obj && obj.allocationsMissingFromQBO;
  }
}
