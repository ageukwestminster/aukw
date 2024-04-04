import { inject } from '@angular/core';
import {
  HttpRequest,
  HttpHandlerFn,
  HttpEvent,
} from '@angular/common/http';
import { Observable } from 'rxjs';

import { environment } from '@environments/environment';
import { AuthenticationService } from '@app/_services';

/**
 * The JWT Interceptor intercepts http requests from the application and modifies them
 * to add JWT authentication credentials to the Authorization header if the user is 
 * logged in and the request is to the application api url (environment.apiUrl).
 * 
 * From: https://jasonwatmore.com/angular-15-16-free-course-7-migrate-to-standalone-components-and-functional-interceptors
 */
export function jwtInterceptor(
  request: HttpRequest<any>,
  next: HttpHandlerFn,
): Observable<HttpEvent<any>> {
  const authenticationService = inject(AuthenticationService);
  // add auth header with jwt if user is logged in and request is to the api url
  const user = authenticationService.userValue;
  const isLoggedIn = user && user.accessToken;
  const isApiUrl = request.url.startsWith(environment.apiUrl);
  if (isLoggedIn && isApiUrl) {
    request = request.clone({
      setHeaders: { Authorization: `Bearer ${user.accessToken}` },
    });
  }

  return next(request);
}
