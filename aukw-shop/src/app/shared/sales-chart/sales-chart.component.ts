import { Component, OnInit } from '@angular/core';
import * as Highcharts from 'highcharts';
import { SalesChartData } from '@app/_models';
import { SummaryService } from '@app/_services';
import { merge, of } from 'rxjs';
import { switchMap } from 'rxjs/operators';

@Component({
  selector: 'sales-chart',
  templateUrl: './sales-chart.component.html',
  styleUrls: ['./sales-chart.component.css'],
})
export class SalesChartComponent implements OnInit {
  public options: Highcharts.Options = {
    title: {
      text: 'Harrow Road Daily Net Sales',
    },
    subtitle: {
      text: 'Compared To Avg of Last 30 days',
    },
    credits: {
      enabled: false,
    },
    yAxis: {
      title: {
        text: 'Daily Sales Less Cash Expenses',
      },
    },

    xAxis: {
      type: 'datetime',
      accessibility: {
        rangeDescription: 'Range: Last 10 Trading Days',
      },
      categories: [],
    },

    legend: {
      layout: 'vertical',
      align: 'right',
      verticalAlign: 'middle',
    },

    series: [
      {
        name: 'Daily Sales',
        data: [],
        type: 'line',
        color: '#FF0000',
      },
      {
        name: 'Average of Last 30 Days',
        data: [],
        type: 'line',
        color: '#008080',
      },
    ],

    responsive: {
      rules: [
        {
          condition: {
            maxWidth: 500,
          },
          chartOptions: {
            legend: {
              layout: 'horizontal',
              align: 'center',
              verticalAlign: 'bottom',
            },
          },
        },
      ],
    },
  };

  constructor(private summaryService: SummaryService) {}

  ngOnInit(): void {
    // First 5 lines convert Observable<MonthlySalesChartData[]> to Observable<MonthlySalesChartData>
    this.summaryService
      .getSalesChartData()
      .pipe(
        switchMap((dataArray: SalesChartData[]) => {
          const obs = dataArray.map((x) => {
            return of(x);
          });
          return merge(...obs);
        })
      )
      .subscribe({
        next: (row: SalesChartData) => {
          const date = new Date(row.date);
          const label =
            date.toLocaleString('en-GB', { day: 'numeric' }) +
            '-' +
            date.toLocaleString('en-GB', { month: 'short' });
          if (this.options.xAxis) {
            if ((this.options.xAxis as Highcharts.XAxisOptions).categories) {
              (this.options.xAxis as Highcharts.XAxisOptions).categories?.push(
                label
              );
            }
          }
          if (this.options.series) {
            this.options.series[0]['data'].push(row.sales);
            this.options.series[1]['data'].push(row.avg30);
          }
        },
        complete: () => {
          Highcharts.chart('sales-chart', this.options);
        },
      });
    // const updated_sales_data: any[] = [];
    // const updated_avg10_data: any[] = [];
    // const updated_avg30_data: any[] = [];
    // const updated_avg365_data: any[] = [];

    // this.summaryService
    // .getSalesChartData()
    // .pipe(
    //   switchMap((x: SalesChartData[]) =>
    //     from(x).pipe(
    //       map((row: SalesChartData) => {
    //         const date = new Date(row.date).getTime();
    //         updated_sales_data.push([date, row.sales]);
    //         updated_avg10_data.push([date, row.avg10]);
    //         updated_avg30_data.push([date, row.avg30]);
    //         updated_avg365_data.push([date, row.avg365]);
    //         return row;
    //       })
    //     )
    //   ),
    //   reduce(
    //     (curr: SalesChartData[], next: SalesChartData) => [...curr, next],
    //     []
    //   )
    // )
    // .subscribe((data) => {
    //   this.options.series[0]['data'] = updated_sales_data;
    //   //this.options.series[1]['data'] = updated_avg10_data;
    //   this.options.series[1]['data'] = updated_avg30_data;
    //   //this.options.series[2]['data'] = updated_avg30_data;
    //   //this.options.series[3]['data'] = updated_avg365_data;
    //   Highcharts.chart('sales-chart', this.options);
    // });
  }
}
