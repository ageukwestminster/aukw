import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';

import { environment } from '@environments/environment';
import { ApiMessage } from '@app/_models';
import { AuditLogService, AuthenticationService } from '@app/_services';

const baseUrl = `${environment.apiUrl}/allocations`;

/**
 * This class has a single method which returns a array of PayRuns
 */
@Injectable({ providedIn: 'root' })
export class AllocationsService {
  private http = inject(HttpClient);
  private auditLogService = inject(AuditLogService);
  private authenticationService = inject(AuthenticationService);

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
}
