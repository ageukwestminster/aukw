import { Injectable } from '@angular/core';
import { Observable, from } from 'rxjs';
import { concatMap, filter, map, scan } from 'rxjs/operators';

import {
  LineItemDetail,
  EmployeeAllocation,
  PayrollJournalEntry,
  IrisPayslip,
} from '@app/_models';

/**
 * This class separates out payslip functions
 */
@Injectable({ providedIn: 'root' })
export class PayrollService {
  constructor() {}

  employeeJournalEntries(
    payslips: IrisPayslip[],
    allocations: EmployeeAllocation[],
  ): Observable<PayrollJournalEntry> {
    return from(payslips).pipe(
      filter((p) => !p.payslipJournalInQBO), // Only add if not already in QBO
      map((p: IrisPayslip) => this.convertPayslipToQBOFormat(p, allocations)), // map into allocated classes
    );
  }

  /**
   * Return an Observable of allocated employer NI costs
   * @param payslips An array of payslips, detailing each employee's salary and ni
   * @param allocations An array of allocation objects that show how to split costs between classes
   * @returns
   */
  employerNIAllocatedCosts(
    payslips: IrisPayslip[],
    allocations: EmployeeAllocation[],
  ): Observable<LineItemDetail> {
    return this.entries(
      payslips,
      allocations,
      (p: IrisPayslip) => p.employerNI,
    );
  }

  /**
   * Return an Observable of allocated employer pension costs
   * @param payslips An array of payslips, detailing each employee's salary and ni
   * @param allocations An array of allocation objects that show how to split costs between classes
   * @returns Observable<Allocation>
   */
  pensionAllocatedCosts(
    payslips: IrisPayslip[],
    allocations: EmployeeAllocation[],
  ): Observable<LineItemDetail> {
    return this.entries(
      payslips,
      allocations,
      (p: IrisPayslip) => p.employerPension,
    );
  }

  /**
   * Return an Observable of allocated salary costs
   * @param payslips An array of payslips, detailing each employee's salary and ni
   * @param allocations An array of allocation objects that show how to split costs between classes
   * @returns Observable<Allocation>
   */
  grossSalaryAllocatedCosts(
    payslips: IrisPayslip[],
    allocations: EmployeeAllocation[],
  ): Observable<LineItemDetail> {
    return this.entries(payslips, allocations, (p: IrisPayslip) => p.totalPay);
  }

  /**
   * Return an Observable of allocations. This is a private function.
   * @param payslips An array of payslips, detailing each employee's salary and ni
   * @param allocations An array of allocation objects that show how to split costs between classes
   * @param property A function that takes an irisPayslip and returns the quantity that is to be allocated
   * @returns Observable<Allocation>
   */
  private entries(
    payslips: IrisPayslip[],
    allocations: EmployeeAllocation[],
    property: (p: IrisPayslip) => number,
  ): Observable<LineItemDetail> {
    return from(payslips).pipe(

      filter((p) => (property && (property(p) != 0))), // Only add if property value is not zero

      concatMap((p) =>
        // this will split each payslip into one or more allocations
        from(allocations.filter((x) => x.payrollNumber == p.payrollNumber)).pipe(
          filter((x) => x.percentage != 0), // Ignore any allocations of 0%

          // loop through each allocation, computing the correct £ amount from the percentage supplied
          scan(
            (acc: any, empAllocation: EmployeeAllocation) => {
              // We are looping through an array of allocations ordered by employee
              // When the employee changes, reset subtotal (i.e. sum) to zero
              if (empAllocation.quickbooksId != acc.entry.employeeId) {
                acc.sum = 0;
              }

              // This is what's left to allocate
              const remainder = property(p) - acc.sum;

              // Make first attempt at calcualted amount, from percentage and pension amount
              let calculatedAllocatedAmount = Number(
                (Math.round(property(p) * empAllocation.percentage) / 100).toFixed(
                  2,
                ),
              );

              // abs(Amount) can never exceed abs(remainder)
              if (property(p) < 0) {
                calculatedAllocatedAmount = Math.max(
                  calculatedAllocatedAmount,
                  remainder,
                );
              } else {
                calculatedAllocatedAmount = Math.min(
                  calculatedAllocatedAmount,
                  remainder,
                );
              }

              // Handle edge case: Calculated amount is close to but less than remainder
              // In that case then use the remainder
              if (Math.abs(remainder - calculatedAllocatedAmount) < 1)
                calculatedAllocatedAmount = Number(remainder.toFixed(2));

              const line = new LineItemDetail({
                quickbooksId: empAllocation.quickbooksId,
                name: empAllocation.name,
                account: empAllocation.account,
                class: empAllocation.class,
                amount: calculatedAllocatedAmount,
              });

              // Send this object back as an accumulator, later we will just take the entry property
              return {
                sum: acc.sum + line.amount,
                entry: line,
              };
            },
            // starting value for accumulator
            {
              sum: 0,
              entry: new LineItemDetail({ quickbooksId: 0, amount: 0 }),
            },
          ),
        ),
      ),
      map((x) => x.entry), // pluck a single property
    );
  }

  /**
   * Return an Observable of employee payslips for the shop. Payslips whose details
   * have already been entered in QBO are filtered out.
   * @param payslips An array of payslips, detailing each employee's salary and ni
   * @param allocations An array of allocation objects that show how to split costs between classes
   * @returns An observable of IrisPayslip objects. Could be empty.
   */
  shopPayslips(
    payslips: IrisPayslip[],
    allocations: EmployeeAllocation[],
  ): Observable<IrisPayslip> {
    const shopEmployees = allocations
      .filter((x) => x.isShopEmployee)
      .map((x) => Number(x.payrollNumber)); // Only need payroll number

    return from(payslips).pipe(
      filter((p) => !p.shopJournalInQBO), // Only add if not already in QBO
      filter((p) => shopEmployees.includes(p.payrollNumber)),
    );
  }

  /**
   * Given an employee's payslip numbers, convert them into an array that can be used
   * to create a journal in QBO.
   * @param p The detailed salary and deduction amounts form an employee's payslip
   * @returns
   */
  private convertPayslipToQBOFormat(
    p: IrisPayslip,
    allocationsArray: EmployeeAllocation[],
  ): PayrollJournalEntry {
    const allocations = allocationsArray.filter(
      (allocation) => allocation.payrollNumber == p.payrollNumber,
    );

    const entry = new PayrollJournalEntry({
      employeeId: allocations[0].quickbooksId,
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
      const alloc = new LineItemDetail({
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
