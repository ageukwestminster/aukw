import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { concatMap} from 'rxjs';
import { QBAuthUri, QBConnectionDetails, QBRealm, User } from '@app/_models';
import {
  AlertService,
  AuthenticationService,
  QBConnectionService,
} from '@app/_services';
/**
 * @QBConnectionRow: A component for the view of single QBO connection
 */
@Component({
  selector: 'tr[qb-connection-row]',
  templateUrl: './row.component.html',
  styleUrls: ['./row.component.css'],
  standalone: true,
  imports: [CommonModule],
})
export class QBConnectionRowComponent {
  user!: User;
  windowHandle: any = null;
  @Input() realm!: QBRealm;
  @Output() onConnectionRevoked: EventEmitter<QBConnectionDetails>;

  constructor(
    private connectionService: QBConnectionService,
    private alertService: AlertService,
    private authenticationService: AuthenticationService,
  ) {
    this.user = this.authenticationService.userValue;
    this.onConnectionRevoked = new EventEmitter();
  }

  get isValidConnection(): boolean {
    if (
      !this.realm ||
      !this.realm.connection ||
      !this.realm.connection!.refreshtokenexpiry
    ) {
      return false;
    } else {
      const refreshExpiry: Date = new Date(
        this.realm.connection!.refreshtokenexpiry,
      );
      const today = new Date();

      if (today > refreshExpiry) return false;
    }
    return true;
  }

  get isLinkCreatedByCurrentUser(): boolean {
    return this.realm.connection?.linkcreatoruserid == this.user.id;
  }

  revokeConnection(e: Event) {
    e.stopPropagation(); // If click propagates it will open the edit member page

    if (
      !this.realm ||
      !this.realm.connection ||
      !this.realm.connection.accesstoken
    )
      return;

    const connection = this.realm.connection;

    connection.isRevoking = true;
    this.connectionService
      .delete(connection.realmid)
      .subscribe(() => {
        this.alertService.success(
          'Connection revoked for ' + connection.companyname,
          {
            keepAfterRouteChange: true,
          },
        );
        connection.isRevoking = false;
        this.onConnectionRevoked.emit(connection);
      });
  }

  refreshConnection(e: Event) {
    e.stopPropagation(); // If click propagates it will open the edit member page

    if (
      !this.realm ||
      !this.realm.connection ||
      !this.realm.connection.accesstoken
    )
      return;

    const connection = this.realm.connection;

    connection.isRefreshing = true;
    this.connectionService
      .refresh(connection.realmid)
      .subscribe(() => {
        this.alertService.success(
          'Connection refreshed for ' + connection.companyname,
          {
            keepAfterRouteChange: true,
          },
        );
        connection.isRefreshing = false;
      });
  }

  addConnection(e: Event) {
    e.stopPropagation();

    this.connectionService.getAuthUri().subscribe((uri: QBAuthUri) => {
      if (uri && uri.authUri) {
        // Open the QB Auth uri in a new tab or window
        this.windowHandle = window.open(uri.authUri);
      }
    });
    return false;
  }

  revokeAndMakeNewConnection(e: Event) {
    e.stopPropagation(); // If click propagates it will open the edit member page

    if (
      !this.realm ||
      !this.realm.connection ||
      !this.realm.connection.accesstoken
    )
      return;

    const connection = this.realm.connection;

    connection.isRevoking = true;
    this.connectionService
      .delete(connection.realmid)
      .pipe(
        concatMap(() => {
          this.alertService.success(
            'Connection revoked for ' + connection.companyname,
            {
              keepAfterRouteChange: true,
            },
          );
          connection.isRevoking = false;
          this.onConnectionRevoked.emit(connection);

          return this.connectionService.getAuthUri();
        })
      ).subscribe((uri: QBAuthUri) => {
        if (uri && uri.authUri) {
          // Open the QB Auth uri in a new tab or window
          this.windowHandle = window.open(uri.authUri);
        }
      });

    return false;

  }

  // Prevents the click event propagating back up to the table row
  // with undesirable consequences
  onClickEvent(e: Event) {
    e.stopPropagation();
  }
}
