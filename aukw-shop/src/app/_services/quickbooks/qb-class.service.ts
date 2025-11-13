import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { QBClass } from '@app/_models';
import { BehaviorSubject, Observable, of, switchMap, tap } from 'rxjs';

const baseUrl = `${environment.apiUrl}/qb`;

/**
 * This class has methods to return lists of QBO Classes (aka projects).
 */
@Injectable({ providedIn: 'root' })
export class QBClassService {
  private http = inject(HttpClient);
  private classesSubject = new BehaviorSubject<QBClass[]>([]);
  private allocatableClassesSubject = new BehaviorSubject<QBClass[]>([]);

  /**
   * Use this Subject to see a lsit of classes from QBO.
   */
  allocatableClasses$ = this.allocatableClassesSubject.asObservable();
  allClasses$ = this.classesSubject.asObservable();

  /**
   * Get a list of the names of all classes
   * @param realmID The company ID for the QBO company.
   * @returns Array of class ids and names. The ids are strings.
   */
  getAll(realmID: string): Observable<QBClass[]> {
    return this.http
      .get<QBClass[]>(`${baseUrl}/${realmID}/classes`)
      .pipe(tap((classes) => this.classesSubject.next(classes)));
  }

  /**
   * Get an adjusted list of the names of all classes that can be used for allocating to projects
   * @param realmID The company ID for the QBO company.
   * @returns Array of class ids and names. The ids are strings.
   */
  getAllocatableClasses(realmID: string): Observable<QBClass[]> {
    const invalidClasses = [
      'AFL',
      'EOC',
      '02 Designated Funds',
      '03 Restricted',
    ];

    return this.http.get<QBClass[]>(`${baseUrl}/${realmID}/classes`).pipe(
      switchMap((classes) => {
        this.classesSubject.next(classes);

        // Change name of 01 unrestricted class to 'Charity Shop' to make clearer.
        const unrestricted = classes.find(
          (cls) => cls.value.toLowerCase() === '01 unrestricted',
        );
        if (unrestricted) {
          unrestricted.value = 'Charity Shop';
          unrestricted.shortName = 'Charity Shop';
        }

        return of(
          // Remove any of the 'invalid' classes
          classes.filter(
            (qbClass) => invalidClasses.indexOf(qbClass.value) === -1,
          ),
        );
      }),
      tap((classes) => this.allocatableClassesSubject.next(classes)),
    );
  }
}
