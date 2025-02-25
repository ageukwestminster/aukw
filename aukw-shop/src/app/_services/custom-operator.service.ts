import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { of, merge, switchMap } from 'rxjs';

/**
 * Provide custom operators for the rxjs pipe function
 */
@Injectable({ providedIn: 'root' })
export class CustomRxjsOperatorsService<T> {
  /**
   * This is a custom rxjs pipe operator that converts Observable<T[]> to Observable<T>
   * @returns A generic function that takes an observable of an array and returns an
   * observable of the element of the array.
   */
  fromArrayToElement(): <T>(source: Observable<T[]>) => Observable<T> {
    return function <T>(source: Observable<T[]>) {
      return source.pipe(
        switchMap((dataArray: T[]) => {
          const obs = dataArray.map((x) => {
            return of(x);
          });
          return merge(...obs);
        }),
      );
    };
  }
}
