import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { EmployeeName } from '@app/_models';
import { Observable } from 'rxjs';

const baseUrl = `${environment.apiUrl}/qb`;

/**
 * This class has a single method which returns a array of employees
 */
@Injectable({ providedIn: 'root' })
export class QBEmployeeService {
  private http = inject(HttpClient);

  /**
   * Get a list of the names of all available employees
   * @param realmID The company ID for the QBO company.
   * @returns Array of employee ids and names
   */
  getAll(realmID: string): Observable<EmployeeName[]> {
    return this.http.get<EmployeeName[]>(`${baseUrl}/${realmID}/employee`);
  }
}
