import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

import { environment } from '@environments/environment';
import { QBAccountListEntry } from '@app/_models';

const baseUrl = `${environment.apiUrl}/transaction-match`;

/**
 * This class has a single method which takes an existing transaction
 * (in the form of a QBAccountListEntry object) and finds creates a
 * matching transaction, as it would look in the other QBO company file.
 *  
 *
 * It uses matching rules stored in the database to make the match and to
 * populate the fields of the new transaction.
 */
@Injectable({ providedIn: 'root' })
export class TradeMatchService {
  private http = inject(HttpClient);

  /**
   * Given an existing transaction, return a matching transaction
   * @param params An object containing the details of the existing transaction
   * @returns QBAccountListEntry
   */
  match(realmID: string, params: any): Observable<any> {
    return this.http.post<any>(`${baseUrl}/${realmID}/match`, params);
  }
}
