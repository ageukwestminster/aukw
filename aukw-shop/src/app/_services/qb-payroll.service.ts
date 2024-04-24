import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import {
  ApiMessage,
  EmployeeAllocation,
  IrisPayslip,
  PayrollJournalEntry,
} from '@app/_models';
import { Observable } from 'rxjs';

const baseUrl = `${environment.apiUrl}/qb`;

/**
 * This class performs a number of payroll-related task on Quickbooks
 */
@Injectable({ providedIn: 'root' })
export class QBPayrollService {
  constructor(private http: HttpClient) {}

  getWhatsAlreadyInQBO(realmID: string, year: string, month: string) {
    return this.http.get<IrisPayslip[]>(
      `${baseUrl}/${realmID}/query/payroll/${year}/${month}`,
    );
  }

  getAllocations(realmID: string): Observable<EmployeeAllocation[]> {
    return this.http.get<EmployeeAllocation[]>(
      `${baseUrl}/${realmID}/employee/allocations`,
    );
  }

  createEmployerNIJournal(realmID: string, params: any, payrollDate: string): Observable<ApiMessage> {
    return this.http.post<any>(
      `${baseUrl}/${realmID}/journal/employerni?payrolldate=${payrollDate}`,
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
}
