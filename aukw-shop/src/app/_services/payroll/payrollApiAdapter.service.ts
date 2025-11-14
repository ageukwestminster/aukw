import { Injectable, inject } from '@angular/core';
import { Observable, map, mergeMap, switchMap, tap, toArray } from 'rxjs';

import { EmployeeAllocation, EmployeeName, IrisPayslip } from '@app/_models';
import { PayrollTransactionsService, QBPayrollService } from '@app/_services';

/**
 * This service adapts Staffology payroll data to QuickBooks Online payroll data
 */
@Injectable({ providedIn: 'root' })
export class PayrollApiAdapterService {
  private qbPayrollService = inject(QBPayrollService);
  private payrollTransactionsService = inject(PayrollTransactionsService);

  /**
   * Adapt Staffology payslips to QuickBooks Online payslips
   * @param payslips$ From Staffology website
   * @param employees Holds name, payrollNumber and quickbooksId for each employee
   * @param projectAllocations
   * @returns
   */
  adaptStaffologyToQuickBooks(
    payslips$: Observable<IrisPayslip[]>,
    employees: EmployeeName[],
    projectAllocations: EmployeeAllocation[],
  ) {
    var returnObj: {
      payslips: IrisPayslip[];
      total: IrisPayslip;
      payrollDate: string;
    } = { payslips: [], total: new IrisPayslip(), payrollDate: '' };

    return payslips$.pipe(
      tap((payslips: IrisPayslip[]) => {
        returnObj.payrollDate = payslips[0]?.payrollDate || '';
      }),

      // Convert from Observable<T[]> to Observable<T>
      mergeMap((payslips: IrisPayslip[]) => payslips),

      // Loop through each payslip
      map((payslip: IrisPayslip) => {
        // loop through all payslips and sum the values
        // to form a new "total" payslip and put in class level variable
        returnObj.total = returnObj.total.add(payslip);

        // Check for missing employees and missing allocations
        var employeeName = employees.find(
          (emp) => emp.payrollNumber === payslip.payrollNumber,
        );
        if (employeeName) {
          payslip.employeeMissingFromQBO = false;
          payslip.quickbooksId = employeeName!.quickbooksId;
        } else {
          payslip.employeeMissingFromQBO = true;
        }

        const allocations = projectAllocations.filter(
          // Note use of '==' instead of '===' because of type difference (string vs number)
          (alloc) => alloc.payrollNumber == payslip.payrollNumber,
        );
        if (allocations && allocations.length) {
          payslip.isShopEmployee = allocations[0].isShopEmployee;
        } else {
          payslip.allocationsMissingFromQBO = true;
        }

        return payslip;
      }),

      // Convert back from Observable<T> to Observable<T[]>
      toArray(),

      // Get payslip flags for Charity QBO ... checking to see if transactions have been entered already
      switchMap((payslips: IrisPayslip[]) => {
        return this.qbPayrollService.payslipFlagsForCharity(
          payslips,
          returnObj.payrollDate,
        );
      }),

      // Get payslip flags for Enterprises QBO
      switchMap((payslips: IrisPayslip[]) => {
        return this.qbPayrollService.payslipFlagsForShop(
          payslips,
          returnObj.payrollDate,
        );
      }),

      // We will use this service to inform other components of the payslips
      map((payslips) => {
        this.qbPayrollService.sendPayslips(payslips);
        this.payrollTransactionsService.createTransactions();

        returnObj.payslips = payslips;
        return returnObj;
      }),
    );
  }
}
