import {
  Component,
  Input,
  OnChanges,
  OnInit,
  SimpleChanges,
} from '@angular/core';
import * as Highcharts from 'highcharts';
import { MovingAverageSalesChartData } from '@app/_models';

@Component({
  selector: 'moving-average',
  templateUrl: './moving-avg-chart.component.html',
  styleUrls: ['./moving-avg-chart.component.css'],
})
export class MovingAverageChartComponent implements OnInit, OnChanges {
  @Input() movingAvgChartData?: MovingAverageSalesChartData;
  public options: Highcharts.Options = {
    title: {
      text: 'Harrow Road Net Sales - Moving Average',
    },
    subtitle: {
      text: 'Averaged over the last 20 & 75 trading days',
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
        rangeDescription: 'Range: October 2021 - Present',
      },
      categories: [],
      labels: {
        formatter: function () {
          return Highcharts.dateFormat('%e %b', this.value as number);
        },
      },
      tickInterval: 1000 * 60 * 60 * 24 * 10, // 10 days
    },

    legend: {
      layout: 'vertical',
      align: 'right',
      verticalAlign: 'middle',
    },

    series: [
      {
        name: 'Average Sales for Last 20 days',
        data: [],
        type: 'line',
        color: '#FF0000',
      },
      {
        name: 'Average Sales for Last 75 days',
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

  constructor() {}

  ngOnInit(): void {}

  ngOnChanges(changes: SimpleChanges) {
    if (changes['movingAvgChartData']) {
      if (this.options.series && this.movingAvgChartData) {
        this.options.series[0]['data'] = this.movingAvgChartData.avg20;
        this.options.series[1]['data'] = this.movingAvgChartData.avgQuarter;

        Highcharts.chart('moving-average', this.options);
      }
    }
  }
}
