import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { EmployeeAllocation, PayrollJournalEntry } from '@app/_models';
import { Observable } from 'rxjs';

const baseUrl = `${environment.apiUrl}/qb`;
const allocationsUrl = `${environment.apiUrl}/qb/employee/allocations`;

/**
 * This class performs a number of payroll-related task on Quickbooks
 */
@Injectable({ providedIn: 'root' })
export class QBPayrollService {
  constructor(private http: HttpClient) {}

  getAllocations(realmID: string): Observable<EmployeeAllocation[]> {
    return this.http.get<EmployeeAllocation[]>(
      `${allocationsUrl}?realmid=${realmID}`,
    );
  }

  createEmployerNIJournal(realmID: string, params: any, payrollDate: string) {
    return this.http.post<any>(
      `${baseUrl}/journal/employerni?realmid=${realmID}&payrolldate=${payrollDate}`,
      params,
    );
  }

  createEmployeeJournal(
    realmID: string,
    params: PayrollJournalEntry,
    payrollDate: string,
  ) {
    return this.http.post<any>(
      `${baseUrl}/journal/employee?realmid=${realmID}&payrolldate=${payrollDate}`,
      params,
    );
  }

  createPensionBill(realmID: string, params: any, payrollDate: string) {
    //console.log(JSON.stringify(params, null, 2));
    return this.http.post<any>(
      `${baseUrl}/bill/pensions?realmid=${realmID}&payrolldate=${payrollDate}`,
      params,
    );
  }

  createShopJournal(realmID: string, params: any, payrollDate: string) {
    return this.http.post<any>(
      `${baseUrl}/journal/enterprises?realmid=${realmID}&payrolldate=${payrollDate}`,
      params,
    );
  }
}
