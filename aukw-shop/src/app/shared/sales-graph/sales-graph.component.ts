import { Component, OnInit } from '@angular/core';
import * as Highcharts from 'highcharts';
import { SalesChartData,Summary } from '@app/_models';
import { SummaryService } from '@app/_services';
import { from, map, reduce, switchMap } from 'rxjs';

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
  
  public options : any = {

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
  
    series: [{
      name: 'Last 10 Trading Days',
      data: []
    }, {
      name: 'Average of Last 10 Days',
      data: []
    }, {
      name: 'Average of Last 30 Days',
      data: []
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

    const updated_sales_data: any[] = [];
    const updated_avg10_data: any[] = [];
    const updated_avg30_data: any[] = [];
    const updated_avg365_data: any[] = [];

    this.summaryService
      .getChartData()
      .pipe(
        switchMap((x:SalesChartData[]) =>  from(x)
          .pipe(
            map((row: SalesChartData) => { 
              const date = new Date(row.date).getTime();
              updated_sales_data.push([date, row.sales]);
              updated_avg10_data.push([date, row.avg10]);
              updated_avg30_data.push([date, row.avg30]);
              updated_avg365_data.push([date, row.avg365]);
              return row;
            })
          )
        ),
        reduce((curr: SalesChartData[], next: SalesChartData) => [...curr, next], [])
      ).subscribe( data => {
        
        this.options.series[0]['data'] = updated_sales_data;
        this.options.series[1]['data'] = updated_avg10_data;
        this.options.series[2]['data'] = updated_avg30_data;
        this.options.series[3]['data'] = updated_avg365_data;
        Highcharts.chart('container', this.options);
      });
  }

}
