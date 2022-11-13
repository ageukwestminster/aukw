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
  public optionsSimpleBarChart: Highcharts.Options = {
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
        type: 'column',
      },
    ],
  };
  public optionsStackedBarChart: Highcharts.Options = {
    chart: {
      type: 'column',
    },
    title: {
      text: 'Average Daily Sales Since COVID',
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
        text: 'Average Daily Sales in £',
      },
    },
    plotOptions: {
      column: {
        stacking: 'normal',
      },
    },
    series: [
      {
        name: 'Linens',
        data: [],
        type: 'column',
      },
      {
        name: 'Books',
        data: [],
        type: 'column',
      },
      {
        name: 'Brica',
        data: [],
        type: 'column',
      },
      {
        name: 'Clothing',
        data: [],
        type: 'column',
      },
    ],
  };

  constructor(private summaryService: SummaryService) {}

  ngOnInit(): void {
    // First 5 lines convert Observable<MonthlySalesChartData[]> to Observable<MonthlySalesChartData>
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
          const label = month + '-' + String(value.year).substring(2);
          if (this.optionsSimpleBarChart.xAxis) {
            if (
              (this.optionsSimpleBarChart.xAxis as Highcharts.XAxisOptions)
                .categories
            ) {
              (
                this.optionsSimpleBarChart.xAxis as Highcharts.XAxisOptions
              ).categories?.push(label);
            }
          }
          if (this.optionsStackedBarChart.xAxis) {
            if (
              (this.optionsStackedBarChart.xAxis as Highcharts.XAxisOptions)
                .categories
            ) {
              (
                this.optionsStackedBarChart.xAxis as Highcharts.XAxisOptions
              ).categories?.push(label);
            }
          }
          if (this.optionsSimpleBarChart.series) {
            this.optionsSimpleBarChart.series[0]['data'].push(value.sales);
          }
          if (this.optionsStackedBarChart.series) {
            this.optionsStackedBarChart.series[3]['data'].push(
              value.avg_clothing
            );
            this.optionsStackedBarChart.series[2]['data'].push(value.avg_brica);
            this.optionsStackedBarChart.series[1]['data'].push(value.avg_books);
            this.optionsStackedBarChart.series[0]['data'].push(
              value.avg_linens
            );
          }
        },
        complete: () => {
          Highcharts.chart('monthly-sales-chart', this.optionsSimpleBarChart);
          Highcharts.chart(
            'monthly-dept-sales-chart',
            this.optionsStackedBarChart
          );
        },
      });
  }
}
