import { Injectable } from '@angular/core';
import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor,
} from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';

import { AuthenticationService, AlertService } from '@app/_services';

/**
 * The Error Interceptor intercepts http responses from the api to check if there were any errors. 
 * If there is a 401 Unauthorized or 403 Forbidden response the user is automatically logged out 
 * of the application, all other errors are re-thrown up to the calling service to be handled.
 *
 * To intercept and inspect HTTP responses returned to the Angular app, the ErrorInterceptor 
 * class implements the HttpInterceptor interface and intercept() method.
 */
@Injectable()
export class ErrorInterceptor implements HttpInterceptor {
  constructor(
    private authenticationService: AuthenticationService,
    private alertService: AlertService,
  ) {}

  intercept(
    request: HttpRequest<any>,
    next: HttpHandler,
  ): Observable<HttpEvent<any>> {
    return next.handle(request).pipe(
      catchError((err) => {
        if (
          [401, 403].includes(err.status) &&
          this.authenticationService.userValue
        ) {
          // auto logout if 401 or 403 response returned from api
          this.authenticationService.logout();
        }

        const error = (err && err.error && err.error.message) || err.statusText;

        if (err.status === 422) {
          this.alertService.error(error);
        }

        console.error(err);
        return throwError(() => new Error(error));
      }),
    );
  }
}
