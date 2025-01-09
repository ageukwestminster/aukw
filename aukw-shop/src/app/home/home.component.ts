import { Component, OnInit } from '@angular/core';
import { NgIf } from '@angular/common';
import { RouterLink } from '@angular/router';
import {
  DateRangeEnum,
  HistogramChartData,
  MovingAverageSalesChartData,
  SalesChartData,
  Summary,
  User,
} from '@app/_models';
import {
  AuthenticationService,
  ReportService,
  SummaryService,
} from '@app/_services';
import { DateRangeAdapter } from '@app/_helpers';
import { concatMap } from 'rxjs/operators';
import {
  DepartmentChartComponent,
  MonthlySalesChartComponent,
  MovingAverageChartComponent,
  SalesChartComponent,
  SalesHistogramChartComponent,
  SummaryTableComponent,
} from '@app/shared';

@Component({
  templateUrl: 'home.component.html',
  standalone: true,
  imports: [
    RouterLink,
    DepartmentChartComponent,
    MonthlySalesChartComponent,
    MovingAverageChartComponent,
    SalesChartComponent,
    SalesHistogramChartComponent,
    SummaryTableComponent,
    NgIf,
  ],
})
export class HomeComponent implements OnInit {
  loading = false;
  user: User;
  summary!: Summary[];
  histogramChartData?: HistogramChartData;
  salesChartData?: SalesChartData;
  movingAvgChartData?: MovingAverageSalesChartData;

  constructor(
    private authenticationService: AuthenticationService,
    private reportService: ReportService,
    private summaryService: SummaryService,
    private dateRangeAdapter: DateRangeAdapter,
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
            DateRangeEnum.LAST_SIX_MONTHS,
          );          
          return this.reportService.getSalesHistogram(
            dtRng.startDate,
            dtRng.endDate,
          );
        }),
        concatMap((response) => {
          this.histogramChartData = response;
          return this.reportService.getMovingAverageSales();
        }),
        concatMap((response) => {
          this.movingAvgChartData = response;
          return this.summaryService.getSalesChartData();
        }),
      )
      .subscribe((response) => {
        this.salesChartData = response;
        this.loading = false;
      });
  }
}
