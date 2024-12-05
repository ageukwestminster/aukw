import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { AuditLog, User } from '@app/_models';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class AuditLogService {
  private readonly auditLogUri = `${environment.apiUrl}/auditlog`;

  constructor(private http: HttpClient) {}

  getAll() : Observable<AuditLog[]> {
    return this.http.get<AuditLog[]>(this.auditLogUri);
  }

  getFilteredList(urlParameters: string) : Observable<AuditLog[]> {
    return this.http.get<AuditLog[]>(
      `${this.auditLogUri}/?${urlParameters}`,
    );
  }

  log(user: User, eventtype: string, description: string, objecttype?: string, objectid?: number) {

    if (!user || (user && !user.id)) return;

    let logentry = new AuditLog();
    logentry.userid = user.id;
    logentry.eventtype = eventtype;
    logentry.description = description;
    if (objecttype) logentry.objecttype = objecttype;
    if (objectid) logentry.objectid = objectid;

    this.http
      .post(this.auditLogUri, logentry)
      .subscribe();
  }

}
