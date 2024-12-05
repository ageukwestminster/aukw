import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';

import { environment } from '@environments/environment';
import { ApiMessage, User } from '@app/_models';
import { AuditLogService, AuthenticationService } from '@app/_services';

const baseUrl = `${environment.apiUrl}/user`;

@Injectable({ providedIn: 'root' })
export class UserService {
  constructor(private http: HttpClient) {}

  private auditLogService = inject(AuditLogService);
  private authenticationService = inject(AuthenticationService);

  getAll() {
    return this.http.get<User[]>(baseUrl);
  }

  getById(id: number) {
    return this.http.get<User>(`${baseUrl}/${id}`);
  }

  create(params: any): Observable<ApiMessage> {
    return this.http.post<ApiMessage>(baseUrl, params);
  }

  update(id: number, params: any) {
    return this.http.put(`${baseUrl}/${id}`, params).pipe(
      tap(() => {
        this.auditLogService.log(
          this.authenticationService.userValue,
          'UPDATE',
          `The user with username=${params.username} has been amended`,
          'user',
          id,
        );
      }),
    );
  }

  delete(id: number) {
    return this.http.delete(`${baseUrl}/${id}`).pipe(
      tap(() => {
        this.auditLogService.log(
          this.authenticationService.userValue,
          'DELETE',
          `The user with id=${id} has been deleted`,
          'user',
          id,
        );
      }),
    );
  }
}
