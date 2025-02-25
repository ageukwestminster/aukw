import {
  Component,
  Input,
  SimpleChanges,
} from '@angular/core';
import Highcharts from 'highcharts/es-modules/masters/highstock.src.js'; // From https://github.com/highcharts/highcharts/issues/14183
import { CashRatioMovingAverageChartData } from '@app/_models'; // defined in _models/chart_data.ts

@Component({
  selector: 'cash-ratio-chart',
  imports: [],
  templateUrl: './cash-ratio-chart.component.html',
  styleUrl: './cash-ratio-chart.component.css'
})
export class CashRatioChartComponent {
  @Input() movingAvgChartData?: CashRatioMovingAverageChartData;
  public options: Highcharts.Options = {
    title: {
      text: 'Ratio of Cash to Total Receipts',
    },
    subtitle: {
      text: 'Rolling avg. over 1 month and 3 months',
    },

    // rangeSelector is a property of stockChart only
    rangeSelector: {
      selected: 2,
      buttons: [
        {
          type: 'month',
          count: 6,
          text: '6m',
          title: 'View 6 months',
        },
        {
          type: 'ytd',
          text: 'YTD',
          title: 'View year to date',
        },
        {
          type: 'year',
          count: 1,
          text: '1y',
          title: 'View 1 year',
        },
        {
          type: 'year',
          count: 5,
          text: '5y',
          title: 'View 5 years',
        },
        {
          type: 'all',
          text: 'All',
          title: 'View all',
        },
      ],
    },

    yAxis: {
      title: {
        text: 'Percentage',
      },
    },

    tooltip: {
      xDateFormat: '%A, %e-%b-%Y',
      shared: true,
    },

    xAxis: {
      type: 'datetime',
      accessibility: {
        rangeDescription: 'Trading Days',
      },
      categories: [],
      labels: {
        formatter: function () {
          return Highcharts.dateFormat('%e %b %y', this.value as number);
        },
      },
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

  ngOnChanges(changes: SimpleChanges) {
    if (changes['movingAvgChartData']) {
      // Only create the chart if there is data...
      if (this.options.series && this.movingAvgChartData) {
        // '<any>' statement added because type checking fails
        (<any>this.options.series[0]).data = this.movingAvgChartData.avg20;
        (<any>this.options.series[1]).data = this.movingAvgChartData.avgQuarter;

        Highcharts.stockChart('moving-average', this.options); // using HighCharts Stock
      }
    }
  }
}
