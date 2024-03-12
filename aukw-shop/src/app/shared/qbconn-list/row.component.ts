import { Component, EventEmitter, Input, Output } from '@angular/core';
import { QBConnectionDetails, User } from '@app/_models';
import { AlertService, AuthenticationService, QBConnectionService } from '@app/_services';
/**
 * @UserRow: A component for the view of single User
 */
@Component({
  selector: 'tr[qb-connection-row]',
  templateUrl: './row.component.html',
})
export class QBConnectionRowComponent {
  user!: User;
  @Input() connection!: QBConnectionDetails;
  @Output() onConnectionRevoked: EventEmitter<QBConnectionDetails>;

  constructor(
    private connectionService: QBConnectionService,
    private alertService: AlertService,
    private authenticationService: AuthenticationService
  ) {
    this.user = this.authenticationService.userValue;
    this.onConnectionRevoked = new EventEmitter();
  }

  revokeConnection(e: Event) {
    e.stopPropagation(); // If click propagates it will open the edit member page

    if (!this.connection || !this.connection.accesstoken) return;

    this.connection.isRevoking = true;
    this.connectionService.delete(this.user.id, this.connection.realmid).subscribe(() => {
      this.alertService.success('Connection revoked for ' + this.connection.companyname, {
        keepAfterRouteChange: true,
      });
      this.onConnectionRevoked.emit(this.connection);
    });
  }


  // Prevents the click event propagating back up to the table row
  // which would open the edit user view
  onClickEvent(e: Event) {
    e.stopPropagation();
  }

}
