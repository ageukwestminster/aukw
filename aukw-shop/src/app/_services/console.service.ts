import { Injectable } from '@angular/core';
import { Observable, Subject } from 'rxjs';
import { EmployeeAllocation, IrisPayslip, LineItemDetail } from '@app/_models';

/**
 * Service class to handle messages sent to the console
 */
@Injectable({ providedIn: 'root' })
export class ConsoleService {
  consoleMessage$: Observable<string>;
  private consoleMessageSubject: Subject<string>;

  constructor() {
    this.consoleMessageSubject = new Subject<string>();
    this.consoleMessage$ = this.consoleMessageSubject.asObservable();
  }

  sendConsoleMessage(message: string) {
    this.consoleMessageSubject.next(message);
  }

  sendAllocationsToConsole(empAllocations: EmployeeAllocation[]) {
    empAllocations.forEach(employeeAllocation => {
        const msg:string = `${employeeAllocation.name} : ${employeeAllocation.percentage}% ` +
        `: ${employeeAllocation.isShopEmployee?'Charity Shop':employeeAllocation.className}`;
        this.consoleMessageSubject.next(msg);
    });
  }

  sendPayslipsToConsole(payslips: IrisPayslip[]) {
    if (!payslips || payslips.length == 0) {this.consoleMessageSubject.next('<none>');}
    payslips.forEach(payslip => {
        const msg:string = `${payslip.employeeName} : Salary:£${payslip.totalPay.toFixed(2)} ` +
        `: Net:£${payslip.netPay.toFixed(2)} : SS:£${payslip.salarySacrifice.toFixed(2)}` +
        `: EE Pens:£${payslip.employeePension.toFixed(2)} : ER Pens:£${payslip.employerPension.toFixed(2)}` +
        `: Loan:£${payslip.studentLoan.toFixed(2)} : EE NI:£${payslip.employeeNI.toFixed(2)}` +
        `: ER NI:£${payslip.employerNI.toFixed(2)}`;
        this.consoleMessageSubject.next(msg);
    });
  }
  sendShopPayslipsToConsole(payslips: IrisPayslip[]) {
    if (!payslips || payslips.length == 0) {this.consoleMessageSubject.next('<none>');}
    payslips.forEach(payslip => {
        const msg:string = `${payslip.employeeName} : Salary:£${payslip.totalPay.toFixed(2)} ` +
        `: NI:£${payslip.employerNI.toFixed(2)} : Pension:£${payslip.employerPension.toFixed(2)}`;
        this.consoleMessageSubject.next(msg);
    });
  }

  sendLineItemDetailToConsole(lines: LineItemDetail[]) {
    if (!lines || lines.length == 0) {this.consoleMessageSubject.next('<none>');}
    lines.forEach(line => {
        const msg:string = `${line.name} : Salary:£${line.amount.toFixed(2)} ` +
        `: Account:£${line.account} : Class:£${line.class}`;
        this.consoleMessageSubject.next(msg);
    });
  }
}
