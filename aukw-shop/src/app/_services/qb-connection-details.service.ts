import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { QBConnectionDetails } from '@app/_models';

const baseUrl = `${environment.apiUrl}/qb/connection`;
const authUrl = `${environment.apiUrl}/qb/auth`;

@Injectable({ providedIn: 'root' })
export class QBConnectionDetailsService {
  constructor(private http: HttpClient) {}

  getById(userid: number) {
    return this.http.get<QBConnectionDetails>(`${baseUrl}/${userid}`);
  }

  getAuthUri() {
    return this.http.get<QBConnectionDetails>(`${authUrl}`);
  }
}
