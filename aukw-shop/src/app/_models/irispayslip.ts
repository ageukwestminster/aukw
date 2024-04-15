/**
 * Define the properties of an employee payslip
 */
/*
    {
        "employeeId": 7,
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
  employeeId: number;
  quickbooksId: number;
  employeeName: string;
  payrollDate: string;
  totalPay: number;
  paye: number;
  employeeNI: number;
  otherDeductions: number;
  salarySacrifice: number;
  studentLoan: number;
  netPay: number;
  employerNI: number;
  employerPension: number;
  employeePension: number;

  isShopEmployee: boolean;
  niJournalInQBO: boolean = false;
  pensionBillInQBO: boolean = false;
  shopJournalInQBO: boolean = false;
  payslipJournalInQBO: boolean = false;

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
    this.employeeId = (obj && obj.employeeId) || null;
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
    this.isShopEmployee = (obj && obj.isShopEmployee) || false;
  }
}
