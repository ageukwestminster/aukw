import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { QBRealm } from '@app/_models';

const baseUrl = `${environment.apiUrl}/qb/realm`;

/**
 * This class has a single method which returns a array of QBO realms
 *
 * Realm data is stored in the database.
 * See {@link ../_models/QBRealm}
 */
@Injectable({ providedIn: 'root' })
export class QBRealmService {
  constructor(private http: HttpClient) {}

  /**
   * Get a list of the names of all available shops
   * @returns Array of shop objects
   */
  getAll() {
    return this.http.get<QBRealm[]>(baseUrl);
  }
}
