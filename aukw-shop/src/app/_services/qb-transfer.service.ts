import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { ApiMessage, QBTransfer } from '@app/_models';
import { Observable } from 'rxjs';

const baseUrl = `${environment.apiUrl}/qb`;

/**
 *
 */
@Injectable({ providedIn: 'root' })
export class QBTransferService {
  private http = inject(HttpClient);

  /**
   * Get a list of the names of all available employees
   * @param realmID The company ID for the QBO company.
   * @returns Array of employee ids and names
   */
  getAll(realmID: string): Observable<QBTransfer[]> {
    return this.http.get<QBTransfer[]>(`${baseUrl}/${realmID}/employee`);
  }

  /**
   * Create a new QBO Transfer in the Enterprises company
   * @param realmID The company ID for the QBO company.
   * @param params The parameters of the transfer to be created. Format: 
   * {
    "txnDate":"2025-02-28",
    "amount": 100,
    "privateNote": "Test transfer"
}
   * @returns The response from the API, including the ID of the created transfer
   */
  create(realmID: string, params: any): Observable<ApiMessage> {
    return this.http.post<ApiMessage>(`${baseUrl}/${realmID}/enterprises-interco`, params);
  }
}
