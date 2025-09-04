import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { QBAttachment, ValueIdType } from '@app/_models';
import { Observable } from 'rxjs';

const baseUrl = `${environment.apiUrl}/qb`;

/**
 * This class is used for managing QBO attachments. It can inspect QB
 * transactions and download their associated attachments. It can also
 * add attachments to existing transactions.
 * It can also copy attachemtns from a transaction in one QBO company
 * file to a transaction or transactions in another QBO company file.
 */
@Injectable({ providedIn: 'root' })
export class QBAttachmentService {
  private http = inject(HttpClient);

  /**
   * Download QBO attachments to the downloads folder, for a given entity
   * @param realmID The company ID for the QBO company.
   * @returns Array of attachment names and content types
   */
  downloadAttachments(
    realmID: string,
    type: string,
    id: number,
  ): Observable<QBAttachment[]> {
    return this.http.get<QBAttachment[]>(
      `${baseUrl}/${realmID}/download-attachments?entity_type=${type}&txn_id=${id}`,
    );
  }

    /**
   * Upload QBO attachments from the downloads folder and attach to a given entity
   * @param realmID The company ID for the QBO company.
    * @param attachmentRefs The references to the attachments to be uploaded
    * @param filenames An array of filenames (with path) to be uploaded
   */
  uploadAttachments(
    realmID: string,
    attachmentRefs: {value: number, type: string}[],
    filenames: {FileName: string, ContentType: string}[],
  ): Observable<any> {

    const params = {
      attachmentRefs: attachmentRefs,
      attachments: { files: filenames },
    };
    return this.http.post<any>(`${baseUrl}/${realmID}/attachments`, params);
  }
}
