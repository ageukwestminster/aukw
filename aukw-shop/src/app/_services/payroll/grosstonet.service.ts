import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { IrisPayslip } from '@app/_models';

const baseUrl = `${environment.apiUrl}/payroll`;

/**
 * This class has a single method which returns a array of Payslips
 */
@Injectable({ providedIn: 'root' })
export class GrossToNetService {
  private http = inject(HttpClient);

  /**
   * Get a array of Payslips for the given employer, tax year and month
   * @returns Array of PayRun objects
   */
  getAll(
    employerID: string,
    taxYear: string,
    month: number,
    sortBy: string | null,
    sortDescending: boolean,
  ): any {
    return this.http.get<IrisPayslip[]>(
      `${baseUrl}/${employerID}/reports/gross-to-net/${taxYear}/month/${month}` + 
      `?sortBy=${sortBy == null ? 'PayrollCode' : sortBy}` + 
      `&sortDescending=${sortDescending ? 'true' : 'false'}`,
    );
  }
}
