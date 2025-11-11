import { Component, Input, OnInit } from '@angular/core';
import Highcharts from 'highcharts/es-modules/masters/highcharts.src.js'; // From https://github.com/highcharts/highcharts/issues/14183

@Component({
  selector: 'tce-by-class-pie-chart',
  templateUrl: './tce-by-class-chart.component.html',
  styleUrls: ['./tce-by-class-chart.component.css'],
  standalone: true,
  imports: [],
})
/** Pie chart of Total Cost of Employement by Class */
export class TCEByClassChartComponent implements OnInit {
  public options: any = {
    chart: {
      plotBackgroundColor: null,
      plotBorderWidth: null,
      plotShadow: false,
      type: 'pie',
    },
    title: {
      text: 'Total Cost of Employment',
    },
    subtitle: {
      text: 'By Class',
    },
    exporting: {
      enabled: true
    },
    tooltip: {
      //https://www.highcharts.com/docs/chart-concepts/labels-and-string-formatting
      pointFormat:
        'Project: £{point.y:,.0f} <b>({point.percentage:.1f}%)</b>',
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
        name: 'Projects',
        colorByPoint: true,
        data: [],
      },
    ],
  };

  @Input() topClasses : [string, string, number, number][] = [];

  constructor() {}

  ngOnInit(): void {
    if (this.topClasses && this.topClasses.length) {
      this.topClasses.forEach((element) => {
        this.options.series[0]['data'].push({
          name: element[1],
          y: element[2],
          sliced: true,
          selected: true,
        });
      });

      Highcharts.chart('tce-by-class-chart', this.options);
    }
  }
}
