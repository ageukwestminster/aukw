import { Component, OnInit } from '@angular/core';
import * as Highcharts from 'highcharts';
import { SummaryService } from '@app/_services';
import { MonthlySalesChartData } from '@app/_models';
import { Observable, merge, of } from 'rxjs';
import { map, switchMap } from 'rxjs/operators';

/* from https://www.highcharts.com/blog/tutorials/highcharts-and-angular-7/ */
declare var require: any;
let Boost = require('highcharts/modules/boost');
let noData = require('highcharts/modules/no-data-to-display');
let More = require('highcharts/highcharts-more');
let Accessibility = require('highcharts/modules/accessibility');

Boost(Highcharts);
noData(Highcharts);
More(Highcharts);
noData(Highcharts);
Accessibility(Highcharts);
noData(Highcharts);

@Component({
  selector: 'monthly-sales-chart',
  templateUrl: './monthly-sales-chart.component.html',
  styleUrls: ['./monthly-sales-chart.component.css'],
})
export class MonthlySalesChartComponent implements OnInit {
  public options1: any = {
    chart: {
      type: 'column',
    },
    title: {
      text: 'Monthly Sales Since COVID',
    },
    subtitle: {
      text: 'Apr 2021 - Present',
    },
    xAxis: {
      categories: [],
    },
    yAxis: {
      title: {
        useHTML: true,
        text: 'Sales in £',
      },
    },
    plotOptions: {
      column: {
        pointPadding: 0.2,
        borderWidth: 0,
      },
    },
    series: [
      {
        name: 'Sales less cash expenses',
        data: [],
      },
    ],
  };

  constructor(private summaryService: SummaryService) {}

  ngOnInit(): void {
    // First 5 lines convert Observable<object[]> to Observable<object>
    this.summaryService
      .getMonthlySalesChartData(1)
      .pipe(
        switchMap((dataArray: MonthlySalesChartData[]) => {
          const obs = dataArray.map((x) => {
            return of(x);
          });
          return merge(...obs);
        })
      )
      .subscribe({
        next: (value: MonthlySalesChartData) => {
          const date = new Date(value.year, value.month, 1);
          const month = date.toLocaleString('en-GB', { month: 'short' });
          this.options1.xAxis.categories.push(
            month + '-' + String(value.year).substring(2)
          );
          this.options1.series[0]['data'].push(value.sales);
        },
        complete: () => {
          Highcharts.chart('monthly-sales-chart', this.options1);
        },
      });
  }
}
