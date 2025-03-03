import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { QBAttachment } from '@app/_models';
import { Observable } from 'rxjs';

const baseUrl = `${environment.apiUrl}/qb`;

/**
 * This class is used for managing QBO attachments. It can inspect QB
 * transactions and download their associated attachments. It can also
 * add attachments to existing transactions. 
 * It can also copy attachemtns from a transaction in one QBO company 
 * file to another QBO company file. 
 */
@Injectable({ providedIn: 'root' })
export class QBAttachmentService {
  private http = inject(HttpClient);

  /**
   * Get a list of the names of all available employees
   * @param realmID The company ID for the QBO company.
   * @returns Array of employee ids and names
   */
  downloadAttachments(realmID: string, type: string, id: string): Observable<QBAttachment[]> {
    return this.http.get<QBAttachment[]>(`${baseUrl}/${realmID}/download-attachments?entity_type=${type}&txn_id=${id}`);
  }
}
