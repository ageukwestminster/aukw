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
      text: 'Harrow Road Shop Sales Trend',
    },
    subtitle: {
      text: 'Daily sales, rolling average over 1 month and 3 months',
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
          return Highcharts.dateFormat('%e %b %y', this.value as number);
        },
      },
      tickInterval: 1000 * 60 * 60 * 24 * 15, // 15 days
    },

    legend: {
      layout: 'vertical',
      align: 'right',
      verticalAlign: 'middle',
    },

    series: [
      {
        name: '1 month rolling average',
        data: [],
        type: 'line',
        color: '#800000',
      },
      {
        name: '3 month rolling average',
        data: [],
        type: 'line',
        color: '#1cb2f5',
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
        // This defensive if statement in place because typescript doesn't know if
        // there is a 'data' property for the general series object.
        if (this.options.series[0]  && this.options.series[0].type === 'line' ) {
          this.options.series[0].data = this.movingAvgChartData.avg20;
        }
        if (this.options.series[1]  && this.options.series[1].type === 'line' ) {
          this.options.series[1].data = this.movingAvgChartData.avgQuarter;
        }

        Highcharts.chart('moving-average', this.options);
      }
    }
   }
}
