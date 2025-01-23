import { inject } from '@angular/core';
import { HttpRequest, HttpHandlerFn, HttpEvent } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';

import { AuthenticationService, AlertService } from '@app/_services';

/**
 * The Error Interceptor intercepts http responses from the api to check if there were any errors.
 * If there is a 401 Unauthorized or 403 Forbidden response the user is automatically logged out
 * of the application, all other errors are re-thrown up to the calling service to be handled.
 *
 * From: {@link https://jasonwatmore.com/angular-15-16-free-course-7-migrate-to-standalone-components-and-functional-interceptors}
 */
export function errorInterceptor(
  request: HttpRequest<any>,
  next: HttpHandlerFn,
): Observable<HttpEvent<any>> {
  const authenticationService = inject(AuthenticationService);
  const alertService = inject(AlertService);
  return next(request).pipe(
    catchError((err) => {
      if (
        [401, 403].includes(err.status) &&
        authenticationService.userValue &&
        !/\/qb\//.test(request.url) // do not logout for 401 errors from QuickBooks
      ) {
        // auto logout if 401 or 403 response returned from api
        authenticationService.logout();
      }

      const error = err.error?.message || err.statusText;

      console.error(err);
      return throwError(() => error);
    }),
  );
}
