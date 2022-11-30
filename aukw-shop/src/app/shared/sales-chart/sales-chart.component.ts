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
      text: 'Harrow Road Daily Net Sales For Last 10 Trading Days',
    },
    subtitle: {
      text: 'Compared To Avg of Last 30 days',
    },
    credits: {
      text: 'Source Data',
      href: '/reports/sales-list',
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
      labels: {
        formatter: function () {
          return Highcharts.dateFormat('%e %b', this.value as number);
        },
      },
      tickInterval: 1000 * 60 * 60 * 24,
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
        marker: {
          enabled: false,
        },
      },
      {
        name: 'Average of Last 365 Days',
        data: [],
        type: 'line',
        marker: {
          enabled: false,
        },
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
    this.summaryService.getSalesChartData().subscribe({
      next: (data: SalesChartData) => {
        if (this.options.series) {
          this.options.series[0]['data'] = data.sales;
          this.options.series[1]['data'] = data.avg30;
          this.options.series[2]['data'] = data.avg365;
          this.options.series[0]['name'] = 'Daily Sales (average £' + data.avg[0][1]+')';
          this.options.series[1]['name'] = '30 day average (£' + data.avg30[0][1]+')';
          this.options.series[2]['name'] = '365 day average (£' + data.avg365[0][1]+')';
        }
      },
      complete: () => {
        Highcharts.chart('sales-chart', this.options);
      },
    });
  }
}
