import { Component, OnInit } from '@angular/core';
import { Summary, User } from '@app/_models';

import {
  AuthenticationService,
  ReportService,
  SummaryService,
} from '@app/_services';
import { DateRangeAdapter } from '@app/_helpers';
import { DateRangeEnum, HistogramChartData } from '@app/_models';

import { concatMap } from 'rxjs/operators';

@Component({ templateUrl: 'home.component.html' })
export class HomeComponent implements OnInit {
  loading = false;
  user: User;
  summary!: Summary[];
  histogramChartData?: HistogramChartData;

  constructor(
    private authenticationService: AuthenticationService,
    private reportService: ReportService,
    private summaryService: SummaryService,
    private dateRangeAdapter: DateRangeAdapter
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {
    this.loading = true;
    this.summaryService
      .getSummary()
      .pipe(
        concatMap((response) => {
          this.summary = response;
          const dtRng = this.dateRangeAdapter.enumToDateRange(
            DateRangeEnum.THIS_YEAR
          );
          return this.reportService.getSalesHistogram(
            dtRng.startDate,
            dtRng.endDate
          );
        })
      )
      .subscribe((response) => {
        this.histogramChartData = response;
        this.loading = false;
      });
  }
}
