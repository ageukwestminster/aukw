import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, forkJoin, of, BehaviorSubject } from 'rxjs';
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
 * This class performs a number of payroll-related tasks on Quickbooks
 */
@Injectable({ providedIn: 'root' })
export class QBPayrollService {
  private http = inject(HttpClient);

  private allocationsSubject = new BehaviorSubject<EmployeeAllocation[]>([]);
  private payslipsSubject = new BehaviorSubject<IrisPayslip[]>([]);

  allocations$ = this.allocationsSubject.asObservable();
  payslips$ = this.payslipsSubject.asObservable();
  
  sendPayslips(payslips: IrisPayslip[]) {
    this.payslipsSubject.next(payslips);
  }

  /**
   * Query Quickbooks online for all payroll-related transactions for a given
   * month and year. The payroll transactions are identified by having a DocNumber
   * of the format 'Payroll-YYYY-MM....'.
   * The transactions are then converted by the API into IrisPayslip objects for
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
   * @returns A success or failure message. A success message will have the quickbooks id of the new transaction.
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

  /**
   * Create a new general journal entry in the Charity Quickbooks file that records the salary and deductions
   * for a single employee.
   * @param params An array of PayrollJournalEntry that specify the employee salary and deductions and account/class pairs.
   * @param payrollDate The transaction date of the journal entry.
   * @returns A success or failure message. A success message will have the quickbooks id of the new transaction.
   */
  createEmployeeJournal(params: PayrollJournalEntry, payrollDate: string) {
    return this.http.post<ApiMessage>(
      `${baseUrl}/${environment.qboCharityRealmID}/journal/employee?payrolldate=${payrollDate}`,
      params,
    );
  }

  /**
   * Create a new pension invoice in the Charity Quickbooks file that records the Employer pension amounts
   * and account and class allocations.
   * @param params An array that specify the employee pension amount and account/class pairs.
   * @param payrollDate The transaction date of the journal entry.
   * @returns A success or failure message. A success message will have the quickbooks id of the new transaction.
   */
  createPensionBill(params: any, payrollDate: string) {
    return this.http.post<ApiMessage>(
      `${baseUrl}/${environment.qboCharityRealmID}/bill/pensions?payrolldate=${payrollDate}`,
      params,
    );
  }

  createShopJournal(params: any, payrollDate: string) {
    return this.http.post<ApiMessage>(
      `${baseUrl}/${environment.qboEnterprisesRealmID}/journal/enterprises?payrolldate=${payrollDate}`,
      params,
    );
  }

  /**
   * Set the 'in Charity Quickbooks' flags for a given array of payslips. There are 3 flags:
   *  i) Is the employer NI amount entered in QB?
   *  ii) Are the employee salary and deductions entered in QB?
   *  iii) Is the employer pension amount entered in QB?
   * The function takes the given payslips, sets or unsets the boolean flags for each payslip
   * and then returns the amended array of payslips.
   * @param xlsxPayslips An array of payslips obtained from the Iris payroll spreadsheet (XLSX)
   * @param year The year in which the payroll run happened e.g. '2024'
   * @param month The month in which the payroll run happened e.g. '03' for March
   * @returns An array of payslips, one for each employee, or an empty array.
   */
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
              // isEqualEmployerNI() is defined in @app/_helpers/payslip-comparer.ts
              employerNI: isEqualEmployerNI(payslip, qbPayslip),
              pensionBill: isEqualPension(payslip, qbPayslip),
              employeeJournal: isEqualPay(payslip, qbPayslip),
              shopJournal: false,
            });
          } else {
            // isEqualEmployerNI() is defined in @app/_helpers/payslip-comparer.ts
            payslip.qbFlags.employerNI = isEqualEmployerNI(payslip, qbPayslip);
            payslip.qbFlags.pensionBill = isEqualPension(payslip, qbPayslip);
            payslip.qbFlags.employeeJournal = isEqualPay(payslip, qbPayslip);
          }
        });
        return x.payrollPayslips;
      }),
    );
  }

  /**
   * Set the 'in Enterprises Quickbooks' flag for a given array of payslips. The flags is
   * set or unset by reference to the employee salary, employer NI and employer pension amounts.
   * The function takes the given payslips, sets or unsets the boolean flags for each payslip
   * and then returns the amended array of payslips.
   * @param xlsxPayslips An array of payslips obtained from the Iris payroll spreadsheet (XLSX)
   * @param year The year in which the payroll run happened e.g. '2024'
   * @param month The month in which the payroll run happened e.g. '03' for March
   * @returns An array of payslips, one for each employee, or an empty array.
   */
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
                // isEqualShopPay() is defined in @app/_helpers/payslip-comparer.ts
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
