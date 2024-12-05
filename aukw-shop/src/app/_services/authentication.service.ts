import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, of } from 'rxjs';
import { map, switchMap, tap } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { Role, User } from '@app/_models';
import { AuditLogService } from './auditlog.service';

/**
 * The authentication service is used to login & logout of the Angular app,
 * it notifies other components when the user logs in & out, and allows access
 * to the currently logged in user.
 */
@Injectable({ providedIn: 'root' })
export class AuthenticationService {
  /**
   * This pattern (private BehaviorSubject<object> & public
   *     Observable<object>) is likely being used because:
   *      1. BehaviourSubject guarantees there is always a valid User
   *      2. Using the Observable() user public property exposes the
   *         data from the subject, but at the same time prevents
   *         having data inadvertently pushed into the subject
   *      3. By having the userValue a public property of an injectable
   *         service, the details of the logged-in user are available
   *         throughout the app.
   *
   *  Further reading: {@link https://medium.com/@benlesh/on-the-subject-of-subjects-in-rxjs-2b08b7198b93 medium}
   */
  private userSubject: BehaviorSubject<User>;

  /**
   * Observable property exposed so that any component can subscribe to be
   * notified when a user logs in, logs out or has their token refreshed.
   */
  public user: Observable<User>;

  /**
   * The windows Id of the timer that is set to expire 1 minute before the access token.
   * This timer is used to trigger the creation of a new access token from the refresh token.
   *
   * Code from: {@link https://stackoverflow.com/a/54507207/6941165 stackoverflow}
   */
  private refreshTokenTimerId: number | undefined;

  constructor(
    private router: Router,
    private http: HttpClient,
    private auditLogService: AuditLogService,
  ) {
    this.userSubject = new BehaviorSubject<User>(new User());
    this.user = this.userSubject.asObservable();
  }

  /**
   * The userValue getter allows other components to quickly get the value of the current user
   * without having to subscribe to the user observable.
   */
  public get userValue(): User {
    return this.userSubject.value;
  }

  /**
   * POSTs the username and password to the API for authentication, on success the api
   * returns the user details and a JWT token which are published to all subscribers.
   * The api also returns a refresh token cookie which is stored by the browser.
   * The method then starts a countdown timer by calling this.startRefreshTokenTimer()
   * to auto refresh the JWT token in the background (silent refresh) one minute before
   * it expires so the user stays logged in.
   *
   * @param username The username of the user
   * @param password The password of the user
   * @returns On success the api returns the user details including a JWT token
   */
  login(username: string, password: string) {
    return this.http
      .post<any>(
        `${environment.apiUrl}/auth`,
        { username, password },
        { withCredentials: true },
      )
      .pipe(
        map((user) => {
          user.isAdmin = user && user.role && user.role === Role.Admin; // Add extra property
          this.userSubject.next(user);
          this.startRefreshTokenTimer();
          this.auditLogService.log(user, 'LOGIN', 'User has logged in');
          return user;
        }),
      );
  }

  /**
   * The logout() method makes a DELETE request to the API to revoke the refresh token that is
   * stored in a browser cookie, cancels the silent refresh running in the background, logs
   * the user out by publishing an empty value to all subscriber components, and finally
   * redirects the user to the login page.
   */
  logout() {

    // Note about order of execution:
    // Must insert log entry before logging out, oitherwise not authorized
    this.auditLogService
      .logAsync(this.userSubject.value, 'LOGOUT', 'User has logged out')
      .pipe(
        switchMap(() => {
          return this.http.delete<any>(`${environment.apiUrl}/auth`, {
            withCredentials: true,
          });
        })
      )
      .subscribe(() => {
        this.stopRefreshTokenTimer();
        this.userSubject.next(new User());
        this.router.navigate(['/login']);
      });
  }

  /**
   * Performs authentication making a request to the API that includes a refresh token cookie.
   * The api also returns a new refresh token cookie which replaces the old one in the browser.
   * The method then starts a countdown timer by calling this.startRefreshTokenTimer() to auto
   * refresh the JWT token in the background (silent refresh) one minute before it expires so
   * the user stays logged in.
   *
   * This method returns an empty User (rather than a 401/403 error) if called from /callback
   * Done this way to allow QBO to callback into the app wiothout being authenticated
   *
   * @returns On success returns the user details including a JWT token
   */
  refreshToken() {
    if (window.location.pathname == '/callback') {
      return of(new User());
    }
    return this.http
      .get<any>(`${environment.apiUrl}/auth/refresh`, { withCredentials: true })
      .pipe(
        map((user) => {
          user.isAdmin = user && user.role && user.role === Role.Admin; // Add extra property
          this.userSubject.next(user); //publish to all subscribers
          this.startRefreshTokenTimer();
          return user;
        }),
      );
  }

  /**
   * Provides an endpoint for the QBO/Intuit OAuth2 authorization flow.
   * @param code A code that can be exchanged for access and refresh tokens
   * @param realmId The id of the QBO company
   * @param state A user-supplied string that is used to check for man-in-the-middle attacks
   * @returns On success returns the user details including a JWT token
   */
  callback(code: string, realmId: string, state: string) {
    return this.http
      .get<any>(
        `${environment.apiUrl}/auth/qb/callback?code=${code}&realmId=${realmId}&state=${state}`,
      )
      .pipe(
        map((user) => {
          user.isAdmin = user && user.role && user.role === Role.Admin; // Add extra property
          this.userSubject.next(user);
          this.startRefreshTokenTimer();
          return user;
        }),
      );
  }

  /**
   * Start a timer that, when fired, will use the refresh token to generate a new
   * access token. The timeout is set for 1 moinute before the expiry of the access token.
   */
  private startRefreshTokenTimer() {
    if (this.userValue && this.userValue.accessToken) {
      // parse json object from base64 encoded jwt token
      const accessToken = JSON.parse(
        atob(this.userValue.accessToken.split('.')[1]),
      );

      // set a timeout to refresh the token a minute before the access token expires
      const expires = new Date(accessToken.exp * 1000);
      const timeout = expires.getTime() - Date.now() - 60 * 1000;

      //use of 'window' instead of Node timer: https://stackoverflow.com/a/54507207/6941165
      this.refreshTokenTimerId = window.setTimeout(
        () => this.refreshToken().subscribe(),
        timeout,
      );
    } else {
      this.stopRefreshTokenTimer();
    }
  }

  /**
   * Stop the timer that is used to generate new access tokens form the refresh token
   */
  private stopRefreshTokenTimer() {
    //use of 'window' instead of Node timer: https://stackoverflow.com/a/54507207/6941165
    window.clearTimeout(this.refreshTokenTimerId);
  }
}
