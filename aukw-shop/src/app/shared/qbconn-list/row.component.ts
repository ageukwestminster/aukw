import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { concatMap } from 'rxjs';
import {
  ApiMessage,
  QBAuthUri,
  QBConnectionDetails,
  QBRealm,
  User,
} from '@app/_models';
import {
  AlertService,
  AuditLogService,
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
  windowHandle: any = null; // used to open the QB authorisation uri
  @Input() realm!: QBRealm;
  @Output() onConnectionRevoked: EventEmitter<QBConnectionDetails>;

  constructor(
    private connectionService: QBConnectionService,
    private alertService: AlertService,
    private authenticationService: AuthenticationService,
    private auditLogService: AuditLogService,
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

  /**
   * Check if the QBConnection was created by the current user.
   * If it is then additional functionality (DELETE) is exposed.
   */
  get isLinkCreatedByCurrentUser(): boolean {
    return this.realm.connection?.linkcreatoruserid == this.user.id;
  }

  revokeConnection(e: Event) {
    e.stopPropagation();

    if (
      !this.realm ||
      !this.realm.connection ||
      !this.realm.connection.accesstoken
    )
      return;

    const connection = this.realm.connection;

    connection.isRevoking = true;
    this.connectionService.delete(connection.realmid).subscribe({
      next: () => {
        this.alertService.success(
          'Connection revoked for ' + connection.companyname,
          {
            keepAfterRouteChange: true,
          },
        );
      },
      error: (error: any) => {},
      complete: () => {
        this.auditLogService.log(
          this.user,
          'DELETE',
          'Revoke QB connection for ' + this.realm.name,
        );
        connection.isRevoking = false;
        this.onConnectionRevoked.emit(connection);
      },
    });
  }

  refreshConnection(e: Event) {
    e.stopPropagation();

    if (
      !this.realm ||
      !this.realm.connection ||
      !this.realm.connection.accesstoken
    )
      return;

    const connection = this.realm.connection;

    connection.isRefreshing = true;
    this.connectionService
      .refresh(connection.realmid, connection.linkcreatoruserid)
      .subscribe(() => {
        this.auditLogService.log(
          this.user,
          'UPDATE',
          'Refresh QB connection for ' + this.realm.name,
        );
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
          this.auditLogService.log(
            this.user,
            'DELETE',
            'Revoke QB connection for ' + this.realm.name,
          );
          this.alertService.success(
            'Connection revoked for ' + connection.companyname,
            {
              keepAfterRouteChange: true,
            },
          );
          connection.isRevoking = false;
          this.onConnectionRevoked.emit(connection);

          return this.connectionService.getAuthUri();
        }),
      )
      .subscribe((uri: QBAuthUri) => {
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
