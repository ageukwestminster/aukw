import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { QBTransfer } from '@app/_models';
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

  createNew() : QBTransfer {
    return new QBTransfer({
      
    });
  }
}
