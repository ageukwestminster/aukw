import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { forkJoin, defaultIfEmpty, Observable, of, switchMap, tap } from 'rxjs';

import { environment } from '@environments/environment';
import { ApiMessage, EmployeeAllocation, EmployeeAllocations, EmployeeName } from '@app/_models';
import { AuditLogService, AuthenticationService, QBEmployeeService } from '@app/_services';

const baseUrl = `${environment.apiUrl}/allocations`;

/**
 * This class has a single method which returns a array of PayRuns
 */
@Injectable({ providedIn: 'root' })
export class AllocationsService {
  private http = inject(HttpClient);
  private auditLogService = inject(AuditLogService);
  private authenticationService = inject(AuthenticationService);
  private qbEmployeeService = inject(QBEmployeeService);

  /**
   * Add allocation(s) to the database.
   * The provided allocation(s) are appended to existing ones.
   * @returns Message of success or failure
   */
  append(params: any): Observable<ApiMessage> {
    return this.http.post<ApiMessage>(`${baseUrl}/append`, params).pipe(
      tap(() => {
        this.auditLogService.log(
          this.authenticationService.userValue,
          'INSERT',
          `Appended project allocations to table.`,
          'Allocation',
        );
      }),
    );
  }

    /**
   * This query returns an array of allocation objects that specify what percentage of
   * employee salary costs must be allocated to what account/class pairs.
   * There will be one or more objects for each employee. The sum of the percentages
   * for each employee must be 100.0.
   * The allocations are stored in the Charity QuickBooks file as a recurring transaction.
   * @returns An array of percentage allocations, one or more for each employee, or an empty array.
   */
  getAllocations(employees: EmployeeName[] = []): Observable<EmployeeAllocations[]> {
    
    var employees$ : Observable<EmployeeName[]>;
    if (employees && employees.length) {
      employees$ = of(employees);
    } else {
      employees$ = this.qbEmployeeService
              .getAll(environment.qboCharityRealmID)
              .pipe(defaultIfEmpty([]));
    }

    return forkJoin({
      employees: employees$,
      allocations: this.http.get<EmployeeAllocation[]>(
        `${environment.apiUrl}/allocations`,
      ),
    }).pipe(
      switchMap((x) => {
        const output : EmployeeAllocations[] = [];

        x.allocations.forEach((element) => {
          const ea = output.find((ea) => ea.name.payrollNumber === element.payrollNumber);
          if (ea) {
            ea.projects.push({percentage: element.percentage, classID: element.class });
          } else {
            const name = x.employees.find((e) => e.payrollNumber === element.payrollNumber);
            if (name) {
              const allocations = [{percentage: element.percentage, classID: element.class }];
              output.push(new EmployeeAllocations({name: name, projects: allocations}));
              }
          }
        });
        return of(output);
      }),
    );
  }  
}
