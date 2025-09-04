import { Component, inject } from '@angular/core';

import { RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';
import {
  NgbDropdownModule,
  NgbNavModule,
  NgbCollapseModule,
} from '@ng-bootstrap/ng-bootstrap';

import { AuthenticationService } from './_services';
import { User } from './_models';
import { AlertComponent } from './shared/alert-component';

@Component({
  selector: 'app',
  templateUrl: 'app.component.html',
  standalone: true,
  imports: [
    AlertComponent,
    NgbCollapseModule,
    NgbDropdownModule,
    NgbNavModule,
    RouterLink,
    RouterLinkActive,
    RouterOutlet,
  ],
})
export class AppComponent {
  user?: User;
  active: any = 1;
  isMenuCollapsed: boolean = true;

  private authenticationService = inject(AuthenticationService);

  constructor() {
    this.authenticationService.user.subscribe((x) => (this.user = x));
  }

  logout() {
    this.authenticationService.logout();
  }
}
