import { Component, inject } from '@angular/core';
import { User } from '@app/_models';
import { AuthenticationService } from '@app/_services';
import { QBConnectionListComponent } from '@app/shared';

@Component({
  standalone: true,
  imports: [QBConnectionListComponent],
  templateUrl: './quickbooksconnection.component.html',
})
export class QuickbooksconnectionComponent {
  user!: User;

  private authenticationService = inject(AuthenticationService);

  constructor() {
    this.user = this.authenticationService.userValue;
  }
}
