import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { ApiMessage, QBConnectionDetails } from '@app/_models';

const baseUrl = `${environment.apiUrl}/qb/connection`;
const authUrl = `${environment.apiUrl}/qb/auth`;
const revokeUrl = `${environment.apiUrl}/qb`;

@Injectable({ providedIn: 'root' })
export class QBConnectionDetailsService {
  constructor(private http: HttpClient) {}

  getDetails() {
    return this.http.get<QBConnectionDetails>(`${baseUrl}`);
  }

  getAuthUri() {
    return this.http.get<QBConnectionDetails>(`${authUrl}`);
  }

  revokeQBConnection() {
    return this.http.delete<ApiMessage>(`${revokeUrl}`);
  }
}
