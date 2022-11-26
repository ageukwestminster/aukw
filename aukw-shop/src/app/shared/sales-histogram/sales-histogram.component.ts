import { Component, OnInit } from '@angular/core';
import * as Highcharts from 'highcharts';
import { TakingsService } from '@app/_services';

@Component({
  selector: 'sales-histogram',
  templateUrl: './sales-histogram.component.html',
  styleUrls: ['./sales-histogram.component.css'],
})
export class SalesHistogramComponent implements OnInit {
  private data: number[] = [];

  public options: Highcharts.Options = {
    title: {
      text: 'Histogram of Daily Sales',
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
        title: { text: 'Daily Sales £' },
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
              '{index}. {point.x:.3f} to {point.x2:.3f}, {point.y}.',
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
      },
      {
        name: "Today's Sales",
        visible: true,
        type: 'scatter',
        data: [[233.9, 233.9]],
        id: 's2',
        marker: {
          radius: 10,
        },
        xAxis: 1,
        yAxis: 0,
      },
    ],
  };

  constructor(private takingsService: TakingsService) {}

  ngOnInit(): void {
    this.takingsService.getSimpleSalesList(1, 250).subscribe({
      next: (result: [number, number]) => {
        if (this.options.series) {
          this.options.series[1]['data'] = result;
        }
      },
      complete: () => {
        Highcharts.chart('sales-histogram', this.options);
      },
    });
  }
}
