import { Component, OnInit } from '@angular/core';
import Highcharts from 'highcharts/es-modules/masters/highcharts.src.js'; // From https://github.com/highcharts/highcharts/issues/14183
import { ReportService } from '@app/_services';
import { DepartmentSalesChartData } from '@app/_models';

@Component({
  selector: 'dept-pie-chart',
  templateUrl: './dept-chart.component.html',
  styleUrls: ['./dept-chart.component.css'],
  standalone: true,
  imports: [],
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

  constructor(private reportService: ReportService) {}

  ngOnInit(): void {
    this.reportService
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
