import { Component, OnInit } from '@angular/core';
import * as Highcharts from 'highcharts';
import { switchMap } from 'rxjs/operators';
import { Chart,Summary } from '@app/_models';
import { SummaryService } from '@app/_services';

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
  selector: 'sales-graph',
  templateUrl: './sales-graph.component.html',
  styleUrls: ['./sales-graph.component.css']
})
export class SalesGraphComponent implements OnInit {
  public options: any = {
    chart: {
      type: 'scatter',
      height: 700
    },
    title: {
      text: 'Sample Scatter Plot'
    },
    credits: {
      enabled: false
    },
    tooltip: {
      formatter: function() {
        return '<b>x: </b>' + Highcharts.dateFormat('%e %b %y %H:%M:%S', (this as any).x) +
          ' <br> <b>y: </b>' + (this as any).y.toFixed(2);
      }
    },
    xAxis: {
      type: 'datetime',
      labels: {
        formatter: function() {
          return Highcharts.dateFormat('%e %b %y', (this as any).value);
        }
      }
    },
    series: [
      {
        name: 'Normal',
        turboThreshold: 500000,
        data: [[new Date('2018-01-25 18:38:31').getTime(), 2]]
      },
      {
        name: 'Abnormal',
        turboThreshold: 500000,
        data: [[new Date('2018-02-05 18:38:31').getTime(), 7]]
      }
    ],

    responsive: {
      rules: [{
        condition: {
          maxWidth: 500
        },
        chartOptions: {
          legend: {
            layout: 'horizontal',
            align: 'center',
            verticalAlign: 'bottom'
          }
        }
      }]
    }
  };

  public options2 : any = {

    title: {
      text: 'Harrow Road Daily Net Sales'
    },
    subtitle: {
      text: 'Compared To Historical Averages'
    },  
    yAxis: {
      title: {
        text: 'Daily Sales Less Cash Expenses'
      }
    },
  
    xAxis: {
      type: 'datetime',
      accessibility: {
        rangeDescription: 'Range: Last 10 Trading Days'
      }
    },
  
    legend: {
      layout: 'vertical',
      align: 'right',
      verticalAlign: 'middle'
    },
  
    plotOptions: {
      series: {
        label: {
          connectorAllowed: false
        },
        pointStart: 0
      }
    },
  
    series: [{
      name: 'Last 10 Trading Days',
      data: [465.55,
        400.3,
        456.7,
        615.05,
        333.7,
        543.5,
        325.91,
        601.5,
        400.77,
        563.1]
    }, {
      name: 'Average of Last 10 Days',
      data: [ 470.61,470.61,470.61,470.61,470.61,470.61,470.61,470.61,470.61,470.61]
    }, {
      name: 'Average of Last 30 Days',
      data: [ 424.02,424.02,424.02,424.02,424.02,424.02,424.02,424.02,424.02,424.02]
    }, {
      name: 'Average of Last 365 Days',
      data: []
    }],
  
    responsive: {
      rules: [{
        condition: {
          maxWidth: 500
        },
        chartOptions: {
          legend: {
            layout: 'horizontal',
            align: 'center',
            verticalAlign: 'bottom'
          }
        }
      }]
    }
  
  };

  constructor(private summaryService: SummaryService) {   }

  ngOnInit(): void {
    //Highcharts.chart('container', this.options2);
  }

}
