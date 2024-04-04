import { catchError, of } from 'rxjs';
import { AuthenticationService } from '@app/_services';

/**
 * The app initializer attempts to automatically login to the Angular app on startup
 * by calling authenticationService.refreshToken() which sends a request to the API
 * for a new JWT auth token. If the user previously logged in (without logging out)
 * the browser will have a valid refresh token cookie that is sent with the request
 * to generate a new JWT. On success the app starts on the home page with the user
 * already logged in, otherwise the login page is displayed.
 *
 * Logic from: {@link https://jasonwatmore.com/post/2022/12/09/angular-execute-an-init-function-before-app-startup-with-an-angular-app-initializer}
 *
 * @param authenticationService A service that can authenticate users
 * @returns A completed Observable
 */
export function appInitializer(authenticationService: AuthenticationService) {
  return () =>
    authenticationService.refreshToken().pipe(
      // catch error to start app on success or failure
      catchError(() => of()),
      // When an Angular app initializer function returns an Observable like this
      // one, the observable must complete before the app can startup. An
      // observable is complete when it has finished emitting all values without
      // any errors. The catchError() operator is used to ensure the observable
      // always reaches the completed state even if the refreshToken() request fails.
    );
}
