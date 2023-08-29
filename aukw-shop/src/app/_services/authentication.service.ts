import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { Role, User } from '@app/_models';

@Injectable({ providedIn: 'root' })
export class AuthenticationService {
  /* This pattern (private BehaviorSubject<object> & public 
       Observable<object>) is likely being used because:
        1. BehaviourSubject guarantees there is always a valid User
        2. Using the asObservable() user public property exposes the
           data from the subject, but at the same time prevents
           having data inadvertently pushed into the subject
        3. By having the userValue a public property of an injectable
           service, the details of the logged-in user are available
           throughout the app.

    Further reading: https://medium.com/@benlesh/on-the-subject-of-subjects-in-rxjs-2b08b7198b93
    */
  private userSubject: BehaviorSubject<User>;
  public user: Observable<User>;

  constructor(
    private router: Router,
    private http: HttpClient,
  ) {
    this.userSubject = new BehaviorSubject<User>(new User());
    this.user = this.userSubject.asObservable();
  }

  public get userValue(): User {
    return this.userSubject.value;
  }

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
          return user;
        }),
      );
  }

  logout() {
    this.http
      .delete<any>(`${environment.apiUrl}/auth/revoke`, {
        withCredentials: true,
      })
      .subscribe({
        next: (result) => {
          // Handle result
          //console.log(result)
        },
        error: (error) => {
          console.log(error);
        },
        complete: () => {
          // 'onCompleted' callback.
          // No errors, route to new page here
          //console.log('Completed.');
        },
      });
    this.stopRefreshTokenTimer();
    this.userSubject.next(new User());
    this.router.navigate(['/login']);
  }

  refreshToken() {
    //console.log(new Date().toLocaleString() + ' Refresh called');
    return this.http
      .get<any>(`${environment.apiUrl}/auth/refresh`, { withCredentials: true })
      .pipe(
        map((user) => {
          user.isAdmin = user && user.role && user.role === Role.Admin; // Add extra property
          this.userSubject.next(user);
          this.startRefreshTokenTimer();
          return user;
        }),
      );
  }

  // helper methods
  private refreshTokenTimeout: number | undefined; //https://stackoverflow.com/a/54507207/6941165

  private startRefreshTokenTimer() {
    if (this.userValue && this.userValue.accessToken) {
      // parse json object from base64 encoded jwt token
      const accessToken = JSON.parse(
        atob(this.userValue.accessToken.split('.')[1]),
      );

      // set a timeout to refresh the token a minute before it expires
      const expires = new Date(accessToken.exp * 1000);
      const timeout = expires.getTime() - Date.now() - 60 * 1000;

      //use of 'window' : https://stackoverflow.com/a/54507207/6941165
      this.refreshTokenTimeout = window.setTimeout(
        () => this.refreshToken().subscribe(),
        timeout,
      );
    } else {
      this.stopRefreshTokenTimer();
    }
  }

  private stopRefreshTokenTimer() {
    window.clearTimeout(this.refreshTokenTimeout); //https://stackoverflow.com/a/54507207/6941165
  }
}
