import {
  Component,
  Input,
  OnChanges,
  OnInit,
  SimpleChanges,
} from '@angular/core';
import Highcharts from 'highcharts/es-modules/masters/highcharts.src.js'; // From https://github.com/highcharts/highcharts/issues/14183
import { HistogramChartData } from '@app/_models';

/* from https://www.highcharts.com/docs/advanced-chart-features/highcharts-typescript-declarations */
import 'highcharts/es-modules/masters/modules/accessibility.src.js';
import 'highcharts/es-modules/masters/modules/exporting.src.js';
import 'highcharts/es-modules/masters/modules/histogram-bellcurve.src.js';
import 'highcharts/es-modules/masters/modules/boost.src.js'; //Always import this last

@Component({
  selector: 'sales-histogram',
  templateUrl: './sales-histogram-chart.component.html',
  styleUrls: ['./sales-histogram-chart.component.css'],
  standalone: true,
  imports: [],
})
export class SalesHistogramChartComponent implements OnInit, OnChanges {
  @Input() histogramChartData?: HistogramChartData;
  private YAXISPOSITION = 250;

  public options: Highcharts.Options = {
    title: {
      text: 'Histogram of Net Daily Sales',
    },
    subtitle: {
      text: '', // Subtitle is set dynamically below
    },

    xAxis: [
      {
        title: { text: 'Sales Date' },
        type: 'datetime',
        alignTicks: false,
        labels: {
          formatter: function () {
            return Highcharts.dateFormat('%e %b %y', this.value as number);
          },
        },
        opposite: true,
        visible: false,
      },
      {
        alignTicks: false,
      },
    ],

    yAxis: [
      {
        title: { text: 'Scatter Daily Sales £' },
        visible: false,
      },
      {
        title: { text: 'Histogram Count' },
        opposite: true,
      },
    ],

    plotOptions: {
      histogram: {
        accessibility: {
          point: {
            valueDescriptionFormat:
              '{index}. {point.x:.2f} to {point.x2:.2f}, {point.y}.',
          },
        },
      },
    },

    series: [
      {
        name: 'Count',
        type: 'histogram',
        xAxis: 1,
        yAxis: 1,
        baseSeries: 's1',
        zIndex: -1,
        tooltip: {
          pointFormat:
            '<span class="small-font">Sales between £{point.x:.2f} & £{point.x2:.2f}</span>' +
            '<br/><b>{point.y} different days</b>',
        },
      },
      {
        name: 'Net Daily Sales',
        visible: false,
        type: 'scatter',
        data: [] as number[],
        id: 's1',
        marker: {
          radius: 1.5,
        },
        showInLegend: false
      },
      {
        name: "Today's Sales",
        visible: true,
        type: 'scatter',
        data: [],
        id: 's2',
        marker: {
          radius: 10,
        },
        xAxis: 1,
        yAxis: 0,
        tooltip: {
          pointFormat: '',
        },
      },
    ],
  };

  constructor() {}

  ngOnInit(): void {}

  ngOnChanges(changes: SimpleChanges) {
    if (changes['histogramChartData']) {
      if (
        this.options.series &&
        this.histogramChartData &&
        this.histogramChartData.data
      ) {
        // '<any>' statement added because type checking fails
        (<any>this.options.series[1]).data = this.histogramChartData.data;

        if (this.histogramChartData.last) {
          const lastSalesDate = new Date(this.histogramChartData.last[1]);
          const lastSalesAmount = this.histogramChartData.last[2];

          // '<any>' statement added because type checking fails
          (<any>this.options.series[2]).data = [
            [lastSalesAmount, this.YAXISPOSITION],
          ];

          // Set a custom Series name
          this.options.series[2]['name'] = this.isToday(lastSalesDate)
            ? "Today's Sales = £" + lastSalesAmount
            : 'Latest Sales = £' + lastSalesAmount;

          // If today's sales are below average then set the
          // data colour to red and update the tooltip.
          // The numbers in the tooltip are rounded to 2 places
          // The duplicated if statement tests are to stop typescript errors
          if (lastSalesAmount < this.histogramChartData.average) {
            // '<any>' statement added because type checking fails
            let series2 = <any>this.options.series[2];
            series2['color'] = 'red';
            series2.tooltip!.pointFormat =
              'Sales for ' +
              lastSalesDate.toDateString() +
              ' below average by £' +
              Math.round(
                (this.histogramChartData.average - lastSalesAmount) * 100,
              ) /
                100;
          } else {
            (<any>this.options.series[2]).tooltip!.pointFormat =
              'Sales for ' +
              lastSalesDate.toDateString() +
              ' above average by £' +
              Math.round(
                (lastSalesAmount - this.histogramChartData.average) * 100,
              ) /
                100;
          }

          //Add a title
          this.options.title = {
            text:
              'Histogram of Net Daily Sales</br>' +
              this.histogramChartData.start +
              ' - ' +
              this.histogramChartData.end,
          };

          //Add a subtitle
          this.options.subtitle!.text =
            'Average during period = £' + this.histogramChartData.average;

          // show the chart
          Highcharts.chart('sales-histogram', this.options);
        }
      }
    }
  }

  /** 'True' if the given date is Today, 'false' otherwise */
  private isToday(date: Date) {
    const today = new Date();

    if (today.toDateString() === date.toDateString()) {
      return true;
    }

    return false;
  }
}
