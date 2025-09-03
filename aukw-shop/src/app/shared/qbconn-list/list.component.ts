import { Component, OnInit, Input } from '@angular/core';

import { QBRealmService } from '@app/_services';
import { QBConnectionDetails, QBRealm } from '@app/_models';
import { QBConnectionRowComponent } from './row.component';

@Component({
  selector: 'qbconn-list',
  templateUrl: 'list.component.html',
  styleUrls: ['list.component.css'],
  standalone: true,
  imports: [QBConnectionRowComponent],
})
export class QBConnectionListComponent implements OnInit {
  @Input() userID: number = 0;

  realms!: QBRealm[];

  constructor(private qbRealmService: QBRealmService) {}

  ngOnInit() {
    this.reloadQBRealms();
  }

  connectionWasRevoked(connection: QBConnectionDetails): void {
    this.reloadQBRealms();
  }

  reloadQBRealms() {
    this.qbRealmService.getAll().subscribe((response: QBRealm[]) => {
      this.realms = response;
    });
  }
}
