import { Component, OnInit } from '@angular/core';
import * as Highcharts from 'highcharts';
import { SummaryService } from '@app/_services';

import { MonthlySalesChartData } from '@app/_models';
import { environment } from '@environments/environment';

import { merge, of } from 'rxjs';
import { switchMap } from 'rxjs/operators';

@Component({
  selector: 'monthly-sales-chart',
  templateUrl: './monthly-sales-chart.component.html',
  styleUrls: ['./monthly-sales-chart.component.css'],
  standalone: true,
  imports: [],
})
/*
 * Create two bar charts displaying  monthly sales using Highcarts
 */
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
        name: 'Clothing',
        data: [],
        type: 'column',
      },
      {
        name: 'Brica',
        data: [],
        type: 'column',
      },
      {
        name: 'Books',
        data: [],
        type: 'column',
      },
      {
        name: 'Linens',
        data: [],
        type: 'column',
      },
    ],
  };

  constructor(private summaryService: SummaryService) {}

  ngOnInit(): void {
    // First 5 lines convert Observable<MonthlySalesChartData[]> to Observable<MonthlySalesChartData>
    this.summaryService
      .getMonthlySalesChartData(environment.HARROWROAD_SHOPID)
      .pipe(
        switchMap((dataArray: MonthlySalesChartData[]) => {
          const obs = dataArray.map((x) => {
            return of(x);
          });
          return merge(...obs);
        }),
      )
      .subscribe({
        next: (value: MonthlySalesChartData) => {
          const date = new Date(value.year, value.month - 1, 1);
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

          /* The elaborate if statements below are to allow typescript to
           * detecxt the presence of the 'data' property.
           */
          if (
            this.optionsSimpleBarChart.series &&
            this.optionsSimpleBarChart.series[0] &&
            this.optionsSimpleBarChart.series[0].type === 'column'
          ) {
            this.optionsSimpleBarChart.series[0].data?.push(value.sales);
          }
          if (this.optionsStackedBarChart.series) {
            if (
              this.optionsStackedBarChart.series[0] &&
              this.optionsStackedBarChart.series[0].type === 'column'
            ) {
              this.optionsStackedBarChart.series[0].data?.push(
                value.avg_clothing,
              );
            }
            if (
              this.optionsStackedBarChart.series[0] &&
              this.optionsStackedBarChart.series[1].type === 'column'
            ) {
              this.optionsStackedBarChart.series[1].data?.push(value.avg_brica);
            }
            if (
              this.optionsStackedBarChart.series[0] &&
              this.optionsStackedBarChart.series[2].type === 'column'
            ) {
              this.optionsStackedBarChart.series[2].data?.push(value.avg_books);
            }
            if (
              this.optionsStackedBarChart.series[0] &&
              this.optionsStackedBarChart.series[3].type === 'column'
            ) {
              this.optionsStackedBarChart.series[3].data?.push(
                value.avg_linens,
              );
            }
          }
        },
        complete: () => {
          // Create two charts
          Highcharts.chart('monthly-sales-chart', this.optionsSimpleBarChart);
          Highcharts.chart(
            'monthly-dept-sales-chart',
            this.optionsStackedBarChart,
          );
        },
      });
  }
}
