import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { forkJoin } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { QBConnectionService } from '@app/_services';
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
  constructor(
    private http: HttpClient,
    private qbConnectionService: QBConnectionService,
  ) {}

  /**
   * Get a list of the names of all available QBO realms, with associated
   * QBConnection details, if any.
   * @returns Observable of Arrays of QBO realms, with connection property populated.
   */
  getAll() {
    // forkJoin accepts an array of Observables and emits an array of the
    // last values of each Observable.
    //  'zip' is similar but emits intermediate values
    return forkJoin({
      realmArray: this.http.get<QBRealm[]>(baseUrl),
      qbconnArray: this.qbConnectionService.getAll(),
    }).pipe(
      map((value) => {
        value.realmArray.forEach((realm: QBRealm) => {
          value.qbconnArray.forEach((element) => {
            if (
              element &&
              element.realmid &&
              realm.realmid == element.realmid
            ) {
              realm.connection = element;
            }
          });
        });
        return value.realmArray;
      }),
    );
  }
}
