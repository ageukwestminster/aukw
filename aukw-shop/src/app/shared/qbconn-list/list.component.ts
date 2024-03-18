import { Component, OnInit } from '@angular/core';
import { from, Observable, of, merge, map, pipe } from 'rxjs';
import { concatMap, switchMap } from 'rxjs/operators';
import {
  AuthenticationService,
  QBConnectionService,
  QBRealmService,
} from '@app/_services';
import { QBConnectionDetails, QBRealm, User } from '@app/_models';

@Component({
  selector: 'qbconn-list',
  templateUrl: 'list.component.html',
})
export class QBConnectionListComponent implements OnInit {
  realms!: QBRealm[];
  user!: User;

  constructor(
    private qbConnectionService: QBConnectionService,
    private authenticationService: AuthenticationService,
    private qbRealmService: QBRealmService,
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {
    this.loadData();
  }

  connectionWasRevoked(connection: QBConnectionDetails): void {
    // this.connections = this.connections.filter((x) => x.realmid !== connection.realmid);
    this.loadData();
  }

  loadData() {
    // First 5 lines convert Observable<QBRealm[]> to Observable<QBRealm>
    this.qbRealmService
      .getAll()
      .pipe(
        concatMap((response: QBRealm[]) => {
          this.realms = response;
          return this.qbConnectionService.getAll(this.user.id);
        }),
        switchMap((dataArray: QBConnectionDetails[]) => {
          const obs = dataArray.map((x) => {
            return of(x);
          });
          return merge(...obs);
        }),
      )
      .subscribe((conndetails: QBConnectionDetails) => {
        // Find the relevant realm and add the connection object
        for (let index = 0; index < this.realms.length; index++) {
          const realm = this.realms[index];
          if (realm && realm.realmid == conndetails.realmid) {
            realm.connection = conndetails; // Add connection to realm
          }
        }
      });
  }
}
