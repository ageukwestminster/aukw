import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

import { environment } from '@environments/environment';
import { QBAccountListEntry } from '@app/_models';

const baseUrl = `${environment.apiUrl}/transaction-match`;

/**
 * This class has a single method which takes an existing intercopmpany
 * transaction (in the form of a QBAccountListEntry object) and, using the
 * rules stored in the database, attempts to create what a matching transaction
 * would look like in the other QBO company.
 *
 * The rules stored in the database are used to make the match and to
 * populate the fields of the new transaction.
 */
@Injectable({ providedIn: 'root' })
export class TradeMatchService {
  private http = inject(HttpClient);

  /**
   * Given an existing transaction, using the
   * rules stored in the database, attempts to create what a matching transaction
   * would look like in the other QBO company.
   * @param params An object containing the details of the existing transaction
   * @returns QBAccountListEntry
   */
  match(realmID: string, params: QBAccountListEntry): Observable<any> {
    return this.http.post<any>(`${baseUrl}/${realmID}/match`, params);
  }
}
