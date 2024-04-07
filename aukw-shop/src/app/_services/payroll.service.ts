import { Injectable } from '@angular/core';
import { Observable, from } from 'rxjs';
import {
  concatMap,
  filter,
  mergeMap,
  map,
  scan,
  tap,
  toArray,
} from 'rxjs/operators';

import {
  AlertService,
  AuthenticationService,
  QBPayrollService,
  QBRealmService,
} from '@app/_services';
import {
  EmployeeAllocation,
  EmployerNIEntry,
  PayrollJournalEntry,
  IrisPayslip,
  PensionAllocation,
  QBRealm,
  User,
  TotalPayAllocation,
} from '@app/_models';


/**
 * This class separates out payslip functions
 */
@Injectable({ providedIn: 'root' })
export class PayrollService {
  constructor() {}

  employeeJournalEntries(payslips: IrisPayslip[], allocations: EmployeeAllocation[]) : Observable<PayrollJournalEntry> {
    return from(payslips).pipe(
      filter((p) => !p.payslipJournalInQBO), // Only add if not already in QBO
      map((p: IrisPayslip) => this.convertPayslipToQBOFormat(p, allocations)), // map into allocated classes
    );
  }

  pensionCosts(payslips: IrisPayslip[], allocations: EmployeeAllocation[]) {

  }

    /**
   * Given an employee's payslip numbers, convert them into an array that can be used
   * to create a journal in QBO.
   * @param p The detailed salary and deduction amounts form an employee's payslip
   * @returns
   */
    private convertPayslipToQBOFormat(p: IrisPayslip, allocationsArray: EmployeeAllocation[]): PayrollJournalEntry {
      const allocations = allocationsArray.filter(
        (allocation) => allocation.payrollNumber == p.employeeId,
      );
  
      const entry = new PayrollJournalEntry({
        employeeId: allocations[0].id,
        totalPay: [],
        paye: p.paye,
        employeeNI: p.employeeNI,
        otherDeductions: p.otherDeductions,
        salarySacrifice: -p.salarySacrifice,
        employeePension: -p.employeePension,
        studentLoan: p.studentLoan,
        netPay: -p.netPay,
        name: p.employeeName,
      });
  
      let sum: number = 0;
      for (const [i, v] of allocations.entries()) {
        const alloc = new TotalPayAllocation({
          class: v.class,
          account: v.account,
          amount: Number(
            (Math.round(p.totalPay * v.percentage) / 100).toFixed(2),
          ),
        });
  
        // The sum of the allocated amounts must equal the starting total
        // If there is a discrepancy then adjust the final allocated amount.
        sum += alloc.amount;
        if (i == allocations.length - 1 && sum != p.totalPay) {
          alloc.amount += p.totalPay - sum;
  
          // Round to avoid numbers like 65.4000000000004
          alloc.amount = Number(alloc.amount.toFixed(2));
        }
        if (alloc.amount) entry.totalPay.push(alloc);
      }
  
      return entry;
    }
}
