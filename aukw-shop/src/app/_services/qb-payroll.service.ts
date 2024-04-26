import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, forkJoin, of, Subject } from 'rxjs';
import { map, tap } from 'rxjs/operators';

import { environment } from '@environments/environment';
import {
  ApiMessage,
  EmployeeAllocation,
  IrisPayslip,
  LineItemDetail,
  PayrollJournalEntry,
  QBTransactionFlags,
} from '@app/_models';
import {
  isEqualPay,
  isEqualPension,
  isEqualEmployerNI,
  isEqualShopPay,
} from '@app/_helpers';

const baseUrl = `${environment.apiUrl}/qb`;

/**
 * This class performs a number of payroll-related task on Quickbooks
 */
@Injectable({ providedIn: 'root' })
export class QBPayrollService {
  private http = inject(HttpClient);

  private allocationsSubject = new Subject<EmployeeAllocation[]>();
  allocations$ = this.allocationsSubject.asObservable();
  private payslipsSubject = new Subject<IrisPayslip[]>();
  payslips$ = this.payslipsSubject.asObservable();
  sendPayslips(payslips: IrisPayslip[]) {
    this.payslipsSubject.next(payslips);
  }

  /**
   * Query Quickbooks online for all payroll-related transactions for a given
   * month and year. The payroll transactions are identified by having a DocNumber
   * of the format 'Payroll-YYYY-MM....'.
   * The transacitons are then converted by the API into IrisPayslip objects for
   * each employee.
   * @param realmID The Quickbooks ID of the company file.
   * @param year The year in which the payroll run happened e.g. '2024'
   * @param month The month in which the payroll run happened e.g. '03' for March
   * @returns An array of payslips, one for each employee, or an empty array.
   */
  getWhatsAlreadyInQBO(realmID: string, year: string, month: string) {
    return this.http.get<IrisPayslip[]>(
      `${baseUrl}/${realmID}/query/payroll/${year}/${month}`,
    );
  }

  /**
   * This query returns an array of allocation objects that specify what percentage of
   * employee salary costs must be allocated to what account/class pairs.
   * There will be one or more objects for each employee. The sum of the percentages
   * for each employee must be 100.0.
   * The allocations are stored in the Charity Quickbooks file as a recurring transaction.
   * @returns An array of percentage allocations, one or more for each employee, or an empty array.
   */
  getAllocations(): Observable<EmployeeAllocation[]> {
    return this.http
      .get<
        EmployeeAllocation[]
      >(`${baseUrl}/${environment.qboCharityRealmID}/employee/allocations`)
      .pipe(tap((result) => this.allocationsSubject.next(result)));
  }

  /**
   * Create a new journal entry in the Charity Quickbooks file that records the Employer NI amounts and
   * account and class allocations.
   * @param params An array of LineItemDetails that specify the employee NI amount and account/class pairs.
   * @param payrollDate The transaction date of the journal entry.
   * @returns A success or failure message. A success message will have the quickbooks id of the new transaciton.
   */
  createEmployerNIJournal(
    params: LineItemDetail[],
    payrollDate: string,
  ): Observable<ApiMessage> {
    return this.http.post<any>(
      `${baseUrl}/${environment.qboCharityRealmID}/journal/employerni?payrolldate=${payrollDate}`,
      params,
    );
  }

  createEmployeeJournal(
    realmID: string,
    params: PayrollJournalEntry,
    payrollDate: string,
  ) {
    return this.http.post<any>(
      `${baseUrl}/${realmID}/journal/employee?payrolldate=${payrollDate}`,
      params,
    );
  }

  createPensionBill(realmID: string, params: any, payrollDate: string) {
    //console.log(JSON.stringify(params, null, 2));
    return this.http.post<any>(
      `${baseUrl}/${realmID}/bill/pensions?payrolldate=${payrollDate}`,
      params,
    );
  }

  createShopJournal(realmID: string, params: any, payrollDate: string) {
    return this.http.post<any>(
      `${baseUrl}/${realmID}/journal/enterprises?payrolldate=${payrollDate}`,
      params,
    );
  }

  payslipFlagsForCharity(
    xlsxPayslips: IrisPayslip[],
    year: string,
    month: string,
  ): Observable<IrisPayslip[]> {
    return forkJoin({
      qbPayslips: this.getWhatsAlreadyInQBO(
        environment.qboCharityRealmID,
        year,
        month,
      ),
      payrollPayslips: of(xlsxPayslips),
    }).pipe(
      map((x) => {
        x.payrollPayslips.forEach((payslip) => {
          const qbPayslip =
            x.qbPayslips.find(
              (item) => item.payrollNumber == payslip.payrollNumber,
            ) ?? new IrisPayslip();

          if (!payslip.qbFlags) {
            payslip.qbFlags = new QBTransactionFlags({
              employerNI: isEqualEmployerNI(payslip, qbPayslip),
              pensionBill: isEqualPension(payslip, qbPayslip),
              employeeJournal: isEqualPay(payslip, qbPayslip),
              shopJournal: false,
            });
          } else {
            payslip.qbFlags.employerNI = isEqualEmployerNI(payslip, qbPayslip);
            payslip.qbFlags.pensionBill = isEqualPension(payslip, qbPayslip);
            payslip.qbFlags.employeeJournal = isEqualPay(payslip, qbPayslip);
          }
        });
        return x.payrollPayslips;
      }),
    );
  }

  payslipFlagsForShop(
    xlsxPayslips: IrisPayslip[],
    year: string,
    month: string,
  ): Observable<IrisPayslip[]> {
    return forkJoin({
      qbPayslips: this.getWhatsAlreadyInQBO(
        environment.qboEnterprisesRealmID,
        year,
        month,
      ),
      payrollPayslips: of(xlsxPayslips),
    }).pipe(
      map((x) => {
        x.payrollPayslips.forEach((payslip) => {
          const qbPayslip = x.qbPayslips.find(
            (item) => item.payrollNumber == payslip.payrollNumber,
          );
          if (qbPayslip) {
            if (!payslip.qbFlags) {
              payslip.qbFlags = new QBTransactionFlags({
                employerNI: false,
                pensionBill: false,
                employeeJournal: false,
                shopJournal: isEqualShopPay(payslip, qbPayslip),
              });
            } else {
              payslip.qbFlags.shopJournal = isEqualShopPay(payslip, qbPayslip);
            }
          }
        });
        return x.payrollPayslips;
      }),
    );
  }
}
