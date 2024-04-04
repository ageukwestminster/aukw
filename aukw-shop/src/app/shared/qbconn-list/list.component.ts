import { Component, OnInit, Input } from '@angular/core';
import { QBRealmService } from '@app/_services';
import { QBConnectionDetails, QBRealm } from '@app/_models';

@Component({
  selector: 'qbconn-list',
  templateUrl: 'list.component.html',
})
export class QBConnectionListComponent implements OnInit {
  @Input() userID: number = 0;

  realms!: QBRealm[];

  constructor(
    private qbRealmService: QBRealmService,
  ) {
  }

  ngOnInit() {
    this.reloadQBRealms();
  }

  connectionWasRevoked(connection: QBConnectionDetails): void {
    this.reloadQBRealms();
  }

  reloadQBRealms() {
    if (!this.userID) return;
    this.qbRealmService
      .getAll(this.userID)
      .subscribe((response: QBRealm[]) => {
        this.realms = response;
      });
  }
}
