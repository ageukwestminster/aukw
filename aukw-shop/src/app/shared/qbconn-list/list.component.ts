import { Component, OnInit, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { QBRealmService } from '@app/_services';
import { QBConnectionDetails, QBRealm } from '@app/_models';
import { QBConnectionRowComponent } from './row.component';

@Component({
  selector: 'qbconn-list',
  templateUrl: 'list.component.html',
  standalone: true,
  imports: [ CommonModule, QBConnectionRowComponent],
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
    if (!this.userID) return;
    this.qbRealmService.getAll(this.userID).subscribe((response: QBRealm[]) => {
      this.realms = response;
    });
  }
}
