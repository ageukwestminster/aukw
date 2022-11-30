import { Component, OnInit } from '@angular/core';
import * as Highcharts from 'highcharts';
import { SummaryService } from '@app/_services';
import { DepartmentSalesChartData } from '@app/_models';

/* from https://www.highcharts.com/blog/tutorials/highcharts-and-angular-7/ */
declare var require: any;
let Boost = require('highcharts/modules/boost');
let Histogram = require('highcharts/modules/histogram-bellcurve');
let noData = require('highcharts/modules/no-data-to-display');
let More = require('highcharts/highcharts-more');
let Accessibility = require('highcharts/modules/accessibility');
let Exporting = require('highcharts/modules/exporting');

Boost(Highcharts);
noData(Highcharts);
More(Highcharts);
Histogram(Highcharts);
Accessibility(Highcharts);
Exporting(Highcharts);

@Component({
  selector: 'dept-pie-chart',
  templateUrl: './dept-chart.component.html',
  styleUrls: ['./dept-chart.component.css'],
})
export class DepartmentChartComponent implements OnInit {
  public options: any = {
    chart: {
      plotBackgroundColor: null,
      plotBorderWidth: null,
      plotShadow: false,
      type: 'pie',
    },
    title: {
      text: 'Sales By Department, YTD',
    },
    subtitle: {
      text: 'Since Jan 1st',
    },
    tooltip: {
      //https://www.highcharts.com/docs/chart-concepts/labels-and-string-formatting
      pointFormat:
        'YTD Sales: £{point.y:,.0f} <b>({point.percentage:.1f}%)</b>',
    },
    accessibility: {
      point: {
        valueSuffix: '%',
      },
    },
    plotOptions: {
      pie: {
        allowPointSelect: true,
        cursor: 'pointer',
        dataLabels: {
          enabled: false,
        },
        showInLegend: true,
      },
    },
    series: [
      {
        name: 'Depts',
        colorByPoint: true,
        data: [],
      },
    ],
  };

  constructor(private summaryService: SummaryService) {}

  ngOnInit(): void {
    this.summaryService
      .getDepartmentBreakdownChartData()
      .subscribe((x: DepartmentSalesChartData) => {
        this.options.series[0]['data'].push({
          name: 'Clothing',
          y: x.YTD.clothing,
          sliced: true,
          selected: true,
        });
        this.options.series[0]['data'].push({ name: 'Brica', y: x.YTD.brica });
        this.options.series[0]['data'].push({ name: 'Books', y: x.YTD.books });
        this.options.series[0]['data'].push({
          name: 'Linens',
          y: x.YTD.linens,
        });
        this.options.series[0]['data'].push({ name: 'Other', y: x.YTD.other });

        Highcharts.chart('dept-chart', this.options);
      });
  }
}
