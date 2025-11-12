import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { ValueIdPair, ValueIdType, ValueStringIdPair } from '@app/_models';
import { Observable } from 'rxjs';

const baseUrl = `${environment.apiUrl}/qb`;

/**
 * This class has methods to return lists of QBO entities.
 */
@Injectable({ providedIn: 'root' })
export class QBEntityService {
  private http = inject(HttpClient);

  /**
   * Get a list of the names of all vendors
   * @param realmID The company ID for the QBO company.
   * @returns Array of vendor ids and names
   */
  getAllVendors(realmID: string): Observable<ValueIdPair[]> {
    return this.http.get<ValueIdPair[]>(`${baseUrl}/${realmID}/entity/vendor`);
  }

  /**
   * Get a list of the names of all available accounts
   * @param realmID The company ID for the QBO company.
   * @returns Array of account ids and names
   */
  getAllAccounts(realmID: string): Observable<ValueIdType[]> {
    return this.http.get<ValueIdType[]>(`${baseUrl}/${realmID}/entity/account`);
  }

  /**
   * Get a list of the names of all customers
   * @param realmID The company ID for the QBO company.
   * @returns Array of customer ids and names
   */
  getAllCustomers(realmID: string): Observable<ValueIdPair[]> {
    return this.http.get<ValueIdPair[]>(
      `${baseUrl}/${realmID}/entity/customer`,
    );
  }

  /**
   * Get a list of the names of all classes
   * @param realmID The company ID for the QBO company.
   * @returns Array of class ids and names. The ids are strings.
   */
  getAllClasses(realmID: string): Observable<ValueStringIdPair[]> {
    return this.http.get<ValueStringIdPair[]>(
      `${baseUrl}/${realmID}/entity/class`,
    );
  }
}
