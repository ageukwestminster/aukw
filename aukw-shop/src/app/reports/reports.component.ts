import { Component, inject } from '@angular/core';
import { Location } from '@angular/common';
import { RouterLink } from '@angular/router';
import { NgbAccordionModule } from '@ng-bootstrap/ng-bootstrap';
import { AuthenticationService } from '@app/_services';
import { User } from '@app/_models';

@Component({
  templateUrl: './reports.component.html',
  standalone: true,
  imports: [NgbAccordionModule, RouterLink],
})
export class ReportsComponent {
  user: User;

  private location = inject(Location);
  private authenticationService = inject(AuthenticationService);

  constructor() {
    this.user = this.authenticationService.userValue;
  }

  /** Return to previous page */
  goBack() {
    this.location.back();
    return false; // don't propagate event
  }
}
