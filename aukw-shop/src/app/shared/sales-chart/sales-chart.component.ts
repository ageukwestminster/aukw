import {
  Component,
  Input,
  OnChanges,
  OnInit,
  SimpleChanges,
} from '@angular/core';
import * as Highcharts from 'highcharts';
import { SalesChartData } from '@app/_models';

@Component({
  selector: 'sales-chart',
  templateUrl: './sales-chart.component.html',
  styleUrls: ['./sales-chart.component.css'],
  standalone: true,
  imports: [],
})
export class SalesChartComponent implements OnInit, OnChanges {
  @Input() salesChartData?: SalesChartData;
  public options: Highcharts.Options = {
    title: {
      text: 'Harrow Road Daily Net Sales For Last 10 Trading Days',
    },
    subtitle: {
      text: 'Compared To Avg of Last 30 & 365 days',
    },
    credits: {
      text: 'Source Data',
      href: '/#/reports/sales-list',
    },
    yAxis: {
      title: {
        text: 'Daily Sales Less Cash Expenses',
      },
    },

    tooltip: {
      xDateFormat: '%A, %e-%b-%Y',
      shared: true,
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
      tickInterval: 1000 * 60 * 60 * 24, // 1 day
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

  constructor() {}

  ngOnInit(): void {}

  ngOnChanges(changes: SimpleChanges) {
    if (changes['salesChartData']) {
      if (this.options.series && this.salesChartData) {
        if (this.options.series[0] && this.options.series[0].type === 'line') {
          this.options.series[0].data = this.salesChartData.sales;
        }
        if (this.options.series[1] && this.options.series[1].type === 'line') {
          this.options.series[1].data = this.salesChartData.avg30;
        }
        if (this.options.series[2] && this.options.series[2].type === 'line') {
          this.options.series[2].data = this.salesChartData.avg365;
        }
        this.options.series[0]['name'] =
          'Daily Sales (average £' + this.salesChartData.avg[0][1] + ')';
        this.options.series[1]['name'] =
          '30 day average (£' + this.salesChartData.avg30[0][1] + ')';
        this.options.series[2]['name'] =
          '365 day average (£' + this.salesChartData.avg365[0][1] + ')';

        Highcharts.chart('sales-chart', this.options);
      }
    }
  }
}
