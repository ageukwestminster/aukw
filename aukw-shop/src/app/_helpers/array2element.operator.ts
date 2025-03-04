import { Observable } from 'rxjs';
import { of, merge, switchMap } from 'rxjs';

/**
 * This is a custom rxjs pipe operator that converts Observable<T[]> to Observable<T>
 * @returns A generic function that takes an observable of an array and returns an
 * observable of the element of the array.
  */
export function fromArrayToElement(): <T>(source: Observable<T[]>) => Observable<T> {
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