import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { EmployeeName } from '@app/_models';
import { Observable } from 'rxjs';

const baseUrl = `${environment.apiUrl}/qb/employee`;

/**
 * This class has a single method which returns a array of employees
 */
@Injectable({ providedIn: 'root' })
export class QBEmployeeService {
  constructor(private http: HttpClient) {}

  /**
   * Get a list of the names of all available employees
   * @returns Array of employee ids and names
   */
  getAll(realmID: string): Observable<EmployeeName[]> {
    return this.http.get<EmployeeName[]>(`${baseUrl}?realmid=${realmID}`);
  }
}
