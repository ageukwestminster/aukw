import { Component, OnInit } from '@angular/core';
import { switchMap } from 'rxjs/operators';
import { Chart,Summary,User } from '@app/_models';

import {
  AuthenticationService,
  SummaryService
} from '@app/_services';

@Component({ templateUrl: 'home.component.html' })
export class HomeComponent implements OnInit {
  loading = false;
  user: User;
  summary!: Summary[];
  chart!: Chart;

  constructor(
    private authenticationService: AuthenticationService,
    private summaryService: SummaryService,
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {
    this.loading = true;
    this.summaryService.getSummary().subscribe((response) => {
      this.summary = response;
      this.loading = false;
    });
    this.summaryService
    .getSummary()
    .pipe(
      switchMap((s: Summary[]) => {
        this.summary = s;
        return this.summaryService.getChartData();
      })
    )
    .subscribe((c: any) => {
      this.chart = c;
      this.loading = false;
    });
    
  }
}