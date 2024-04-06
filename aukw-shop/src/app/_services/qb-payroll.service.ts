import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { EmployeeAllocation, EmployerNIEntry, PayrollJournalEntry } from '@app/_models';
import { Observable } from 'rxjs';

const baseUrl = `${environment.apiUrl}/qb`;
const allocationsUrl = `${environment.apiUrl}/qb/employees/allocations`;

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

  createEmployerNIJournal(
    realmID: string,
    params: EmployerNIEntry[],
    payrollDate: string,
  ) {    
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
    //console.log(JSON.stringify(params,null,2));
    return this.http.post<any>(
      `${baseUrl}/journal/employee?realmid=${realmID}&payrolldate=${payrollDate}`,
      params,
    );
  }
}
