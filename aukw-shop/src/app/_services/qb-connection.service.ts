import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { ApiMessage, QBAuthUri, QBConnectionDetails } from '@app/_models';
import { AuthenticationService } from '@app/_services';
import { User } from '@app/_models';

const baseUrl = `${environment.apiUrl}/qb`;
const authUrl = `${environment.apiUrl}/auth/qb/auth`;
const refreshUrl = `${environment.apiUrl}/qb/refresh`;

@Injectable({ providedIn: 'root' })
export class QBConnectionService {
  user!: User;

  constructor(
    private http: HttpClient,
    private authenticationService: AuthenticationService,
  ) {
    this.user = this.authenticationService.userValue;
  }

  /**
   * Get details of the current connection to QuickBooks
   * @returns
   */
  getDetails(realmid: string) {
    return this.http.get<QBConnectionDetails>(
      `${baseUrl}/${realmid}/connection`,
    );
  }

  /**
   * Get details of all the current connections to QuickBooks
   * @returns
   */
  getAll() {
    return this.http.get<QBConnectionDetails[]>(
      `${baseUrl}/connections`,
    );
  }

  getAuthUri() {
    return this.http.get<QBAuthUri>(`${authUrl}`);
  }

  delete(realmid: string) {
    return this.http.delete<ApiMessage>(
      `${baseUrl}/${realmid}/connection`,
    );
  }

  refresh(realmid: string) {
    return this.http.get<ApiMessage>(`${baseUrl}/${realmid}/refresh`);
  }
}
