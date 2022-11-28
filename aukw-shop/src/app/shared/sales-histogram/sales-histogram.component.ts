import { Component, OnInit } from '@angular/core';
import * as Highcharts from 'highcharts';
import { ReportService } from '@app/_services';
import { environment } from '@environments/environment';
import { DateRange, DateRangeEnum, HistogramChartData } from '@app/_models';
import { DateRangeAdapter } from '@app/_helpers';

@Component({
  selector: 'sales-histogram',
  templateUrl: './sales-histogram.component.html',
  styleUrls: ['./sales-histogram.component.css'],
})
export class SalesHistogramComponent implements OnInit {
  public options: Highcharts.Options = {
    title: {
      text: 'Histogram of Net Daily Sales',
    },

    subtitle: {
      text: '',
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

  constructor(private reportService: ReportService,
    private dateRangeAdapter: DateRangeAdapter,
    ) {}

  ngOnInit(): void {
    const YAXISPOSITION = 250;

    let dtRng: DateRange =
      this.dateRangeAdapter.enumToDateRange(DateRangeEnum.THIS_YEAR);

    this.reportService
      .getSalesHistogram(dtRng.startDate,dtRng.endDate,environment.HARROWROAD_SHOPID)
      .subscribe({
        next: (result: HistogramChartData) => {
          if (this.options.series) {
            if (result.data) {
              this.options.series[1]['data'] = result.data;
              const length = result.data.length;
              if (
                length &&
                result.data[length - 1] &&
                result.data[length - 1][1]
              ) {

                this.options.series[2]['data'] = [
                  [result.data[length - 1][1], YAXISPOSITION],
                ];

                // Set a custom Series name
                this.options.series[2]['name'] =
                  "Today's Sales = £" + result.data[length - 1][1];

                // If today's sales are below average then set the 
                // data colour to red
                if (result.data[length - 1][1] < result.average) {
                  this.options.series[2]['color'] = 'red';
                }

                //Add a subtitle
                this.options.subtitle!.text = 'Average during period = £'+result.average,2;
              }
            }
          }
        },
        complete: () => {
          Highcharts.chart('sales-histogram', this.options);
        },
      });
  }
}
