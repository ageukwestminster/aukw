import { Component, OnInit } from '@angular/core';
import { Observable } from 'rxjs';
import {
  AuthenticationService,
  QBConnectionService,
} from '@app/_services';
import { QBConnectionDetails, User } from '@app/_models';

@Component({ templateUrl: 'list.component.html' })
export class QBConnectionListComponent implements OnInit {
  connections!: QBConnectionDetails[]; 
  user!: User;

  constructor(
    private qbConnectionService: QBConnectionService,
    private authenticationService: AuthenticationService,
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {
    this.qbConnectionService.getAll(this.user.id).subscribe((connections) => {
      this.connections = connections;      
    });
  }

  connectionWasRevoked(connection: QBConnectionDetails): void {
    this.connections = this.connections.filter((x) => x.realmid !== connection.realmid);
  }
}
