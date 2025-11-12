import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { PayRun } from '@app/_models';
import { Observable } from 'rxjs';

const baseUrl = `${environment.apiUrl}/payroll`;

/**
 * This class has a single method which returns a array of PayRuns
 */
@Injectable({ providedIn: 'root' })
export class PayRunService {
  private http = inject(HttpClient);

  /**
   * Get a list of the names of all available PayRuns
   * @returns Array of PayRun objects
   */
  getAll(employerID: string, taxYear: string): Observable<PayRun[]> {
    return this.http.get<PayRun[]>(
      `${baseUrl}/${employerID}/payrun/${taxYear}`,
    );
  }

  /**
   * Get the most recent 'Closed' Pay Run
   * @returns Array of PayRun objects
   */
  getLatest(employerID: string): Observable<PayRun> {
    return this.http.get<PayRun>(`${baseUrl}/${employerID}/payrun/most-recent`);
  }
}
