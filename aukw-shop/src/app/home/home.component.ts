import { Component, OnInit } from '@angular/core';
import { Summary, User } from '@app/_models';

import { AuthenticationService, SummaryService } from '@app/_services';

@Component({ templateUrl: 'home.component.html' })
export class HomeComponent implements OnInit {
  loading = false;
  user: User;
  summary!: Summary[];

  constructor(
    private authenticationService: AuthenticationService,
    private summaryService: SummaryService
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {
    this.loading = true;
    this.summaryService.getSummary().subscribe((response) => {
      this.summary = response;
      this.loading = false;
    });
  }
}
