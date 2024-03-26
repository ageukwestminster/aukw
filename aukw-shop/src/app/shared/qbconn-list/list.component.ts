import { Component, OnInit } from '@angular/core';
import { AuthenticationService, QBRealmService } from '@app/_services';
import { QBConnectionDetails, QBRealm, User } from '@app/_models';

@Component({
  selector: 'qbconn-list',
  templateUrl: 'list.component.html',
})
export class QBConnectionListComponent implements OnInit {
  realms!: QBRealm[];
  user!: User;

  constructor(
    private authenticationService: AuthenticationService,
    private qbRealmService: QBRealmService,
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {
    this.reloadQBRealms();
  }

  connectionWasRevoked(connection: QBConnectionDetails): void {
    this.reloadQBRealms();
  }

  reloadQBRealms() {
    this.qbRealmService
      .getAll(this.user.id)
      .subscribe((response: QBRealm[]) => {
        this.realms = response;
      });
  }
}
