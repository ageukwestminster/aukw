import { Component, inject, OnInit } from '@angular/core';
import { Location, NgIf } from '@angular/common';
import { RouterLink } from '@angular/router';
import { NgbAccordionModule } from '@ng-bootstrap/ng-bootstrap';
import { AuthenticationService } from '@app/_services';
import { User } from '@app/_models';

@Component({
  templateUrl: './reports.component.html',
  standalone: true,
  imports: [NgbAccordionModule, NgIf, RouterLink],
})
export class ReportsComponent implements OnInit {
  user: User;

  private location = inject(Location);
  private authenticationService = inject(AuthenticationService);

  constructor() {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit(): void {}

  goBack() {
    this.location.back();
    return false; // don't propagate event
  }
}
