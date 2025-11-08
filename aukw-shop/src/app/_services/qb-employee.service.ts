import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';

import { environment } from '@environments/environment';
import { ApiMessage, EmployeeName } from '@app/_models';
import { AuditLogService, AuthenticationService } from '@app/_services';

const baseUrl = `${environment.apiUrl}/qb`;

/**
 * This class has a single method which returns a array of employees
 */
@Injectable({ providedIn: 'root' })
export class QBEmployeeService {
  private http = inject(HttpClient);
  private auditLogService = inject(AuditLogService);
  private authenticationService = inject(AuthenticationService);

  /**
   * Get a list of the names of all available employees
   * @param realmID The company ID for the QBO company.
   * @returns Array of employee ids and names
   */
  getAll(realmID: string): Observable<EmployeeName[]> {
    return this.http.get<EmployeeName[]>(`${baseUrl}/${realmID}/employee`);
  }

  /**
   * Create a new QBO employee
   * @param realmID The company ID for the QBO company.
   * @param params The details of the employee to add
   * @returns A success or failure message. A success message will have the quickbooks id of the new employee.
   */
  create(realmID: string, params: any): Observable<ApiMessage> {
    return this.http
      .post<ApiMessage>(`${baseUrl}/${realmID}/employee`, params)
      .pipe(
        tap((message: ApiMessage) => {
          this.auditLogService.log(
            this.authenticationService.userValue,
            'INSERT',
            `Added employee with id=${message.id} to QuickBooks`,
            'Employee',
            message.id,
          );
        }),
      );
  }
}
