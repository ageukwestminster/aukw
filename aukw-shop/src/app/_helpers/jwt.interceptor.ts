import { Injectable } from '@angular/core';
import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor,
} from '@angular/common/http';
import { Observable } from 'rxjs';

import { environment } from '@environments/environment';
import { AuthenticationService } from '@app/_services';

/**
 * The JWT Interceptor intercepts http requests from the application to add JWT authentication
 * credentials to the Authorization header if the user is logged in and the request is to the
 * application api url (environment.apiUrl).
 *
 * To intercept and modify HTTP requests sent from the Angular app, the JwtInterceptor
 * class implements the HttpInterceptor interface and intercept() method.
 */
@Injectable()
export class JwtInterceptor implements HttpInterceptor {
  constructor(private authenticationService: AuthenticationService) {}

  intercept(
    request: HttpRequest<any>,
    next: HttpHandler,
  ): Observable<HttpEvent<any>> {
    // add auth header with jwt if user is logged in and request is to the api url
    const user = this.authenticationService.userValue;
    const isLoggedIn = user && user.accessToken;
    const isApiUrl = request.url.startsWith(environment.apiUrl);
    if (isLoggedIn && isApiUrl) {
      request = request.clone({
        setHeaders: { Authorization: `Bearer ${user.accessToken}` },
      });
    }

    return next.handle(request);
  }
}
