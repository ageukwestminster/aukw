import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { ValueIdPair, ValueIdType, ValueStringIdPair } from '@app/_models';
import { Observable } from 'rxjs';

const baseUrl = `${environment.apiUrl}/qb`;

/**
 * This class has methods to return lists of QBO Classes (aka projects).
 */
@Injectable({ providedIn: 'root' })
export class QBClassService {
  private http = inject(HttpClient);

  /**
   * Get a list of the names of all classes
   * @param realmID The company ID for the QBO company.
   * @returns Array of class ids and names. The ids are strings.
   */
  getAll(realmID: string): Observable<ValueStringIdPair[]> {
    return this.http.get<ValueStringIdPair[]>(
      `${baseUrl}/${realmID}/class`,
    );
  }
}