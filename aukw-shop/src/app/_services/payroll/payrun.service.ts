import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { PayRun } from '@app/_models';

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
  getAll(employerID: string, taxYear: string): any {
    return this.http.get<PayRun[]>(`${baseUrl}/${employerID}/payrun/${taxYear}`);
  }
}
