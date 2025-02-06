import { LineItemDetail } from './line-item-detail';
import { PayrollIdentifier } from '@app/_interfaces/payroll-identifier';

export class PayrollJournalEntry implements PayrollIdentifier {
  /** Iris payroll number for employee */
  payrollNumber: number;
  /** QuickBooks employee id */
  quickbooksId: number;
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
  /** Display name of employee. This is required and used in the 
   * creation of the DocNumber of the payroll journal. */
  employeeName: string;

  constructor(obj?: any) {
    this.payrollNumber = (obj && obj.payrollNumber) || null;
    this.quickbooksId = (obj && obj.quickbooksId) || null;
    this.employeeName = (obj && obj.employeeName) || null;
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
