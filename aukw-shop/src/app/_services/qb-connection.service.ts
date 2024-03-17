import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { ApiMessage, QBAuthUri, QBConnectionDetails } from '@app/_models';
import { AuthenticationService} from '@app/_services';
import { Role, User } from '@app/_models';

const baseUrl = `${environment.apiUrl}/qb/connection`;
const authUrl = `${environment.apiUrl}/auth/qb/auth`;
const refreshUrl = `${environment.apiUrl}/qb/refresh`;

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
  getDetails(userid: number, realmid: string) {
    return this.http.get<QBConnectionDetails>(`${baseUrl}/${userid}?realmid=${realmid}`);
  }

  /**
   * Get details of all the current connections to QuickBooks
   * @returns The uri needed to re-authorize the connection and the expiry date of the refresh token
   */
  getAll(userid: number) {
    return this.http.get<QBConnectionDetails[]>(`${baseUrl}s/${userid}`);
  }

  getAuthUri() {
    return this.http.get<QBAuthUri>(`${authUrl}`);
  }

  getAuthUriWithParameters(realmid: string, clientID: string, clientSecret: string, useSandbox: boolean = false) {
    return this.http.post<QBAuthUri>(`${authUrl}?realmid=${realmid}`,{clientID, clientSecret, useSandbox});
  }

  delete(userid : number, realmid : string) {
    return this.http.delete<ApiMessage>(`${baseUrl}/${userid}?realmid=${realmid}`);
  }

  refresh(userid : number, realmid : string) {
    return this.http.get<ApiMessage>(`${refreshUrl}/${userid}?realmid=${realmid}`);
  }

}
