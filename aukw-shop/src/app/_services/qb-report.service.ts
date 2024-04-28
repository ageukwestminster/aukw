import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { Observable } from 'rxjs';
import { QBAccountListEntry } from '@app/_models/qb-account-list-entry';

const baseUrl = `${environment.apiUrl}/qb`;

/**
 * This class is used to retrieve reports from QBO
 */
@Injectable({ providedIn: 'root' })
export class QBReportService {
  private http = inject(HttpClient);

  /**
   * Get a list of transactions for the AUKW account in Enterprises
   * @param realmID The company ID for the QBO company.
   * @returns Array of employee ids and names
   */
  getIntercoAccountLedger(start: string = '', end: string = ''): Observable<QBAccountListEntry[]> {
    return this.http.get<QBAccountListEntry[]>(`${baseUrl}/${environment.qboEnterprisesRealmID}/report/generalledger`+
      `?start=${start}&end=${end}&account=80&sortDescending`
    );
  }
}
