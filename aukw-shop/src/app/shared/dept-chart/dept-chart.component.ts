import { Component, OnInit } from '@angular/core';
import * as Highcharts from 'highcharts';

/* from https://www.highcharts.com/blog/tutorials/highcharts-and-angular-7/ */
declare var require: any;
let Boost = require('highcharts/modules/boost');
let noData = require('highcharts/modules/no-data-to-display');
let More = require('highcharts/highcharts-more');
let Accessibility = require('highcharts/modules/accessibility');

Boost(Highcharts);
noData(Highcharts);
More(Highcharts);
noData(Highcharts);
Accessibility(Highcharts);
noData(Highcharts);

@Component({
  selector: 'dept-chart',
  templateUrl: './dept-chart.component.html',
  styleUrls: ['./dept-chart.component.css']
})
export class DepartmentChartComponent implements OnInit {
  
  public options : any = {
    chart: {
      plotBackgroundColor: null,
      plotBorderWidth: null,
      plotShadow: false,
      type: 'pie'
    },
    title: {
      text: 'Browser market shares in March, 2022'
    },
    tooltip: {
      pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    accessibility: {
      point: {
        valueSuffix: '%'
      }
    },
    plotOptions: {
      pie: {
        allowPointSelect: true,
        cursor: 'pointer',
        dataLabels: {
          enabled: false
        },
        showInLegend: true
      }
    },
    series: [{
      name: 'Brands',
      colorByPoint: true,
      data: [{
        name: 'Chrome',
        y: 74.77,
        sliced: true,
        selected: true
      },  {
        name: 'Edge',
        y: 12.82
      },  {
        name: 'Firefox',
        y: 4.63
      }, {
        name: 'Safari',
        y: 2.44
      }, {
        name: 'Internet Explorer',
        y: 2.02
      }, {
        name: 'Other',
        y: 3.28
      }]
    }]
  };

  constructor() {   }

  ngOnInit(): void {

    Highcharts.chart('dept-chart', this.options);
   
  }

}
