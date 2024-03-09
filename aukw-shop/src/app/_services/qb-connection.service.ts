import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { ApiMessage, QBAuthUri, QBConnectionDetails } from '@app/_models';
import { AuthenticationService} from '@app/_services';
import { Role, User } from '@app/_models';

const baseUrl = `${environment.apiUrl}/qb/connection`;
const authUrl = `${environment.apiUrl}/auth/qb/auth`;
const revokeUrl = `${environment.apiUrl}/qb`;
const realmID = `${environment.quickbooksRealmID}`;

@Injectable({ providedIn: 'root' })
export class QBConnectionService {
  user!: User;

  constructor(private http: HttpClient,
    private authenticationService: AuthenticationService) {
      this.user = this.authenticationService.userValue;
    }

  /**
   * Get details of the current connection to QuickBooks
   * @returns The uri needed to re-authorize the connection and the expiry date of the refresh token
   */
  getDetails() {
    return this.http.get<QBConnectionDetails>(`${baseUrl}?realmid=${realmID}&userid=${this.user.id}`);
  }

  getAuthUri() {
    return this.http.get<QBAuthUri>(`${authUrl}`);
  }

  getAuthUriWithParameters(clientID: string, clientSecret: string, useSandbox: boolean = false) {
    return this.http.post<QBAuthUri>(`${authUrl}`,{clientID, clientSecret, useSandbox});
  }

  revokeQBConnection() {
    return this.http.delete<ApiMessage>(`${revokeUrl}`);
  }

}
