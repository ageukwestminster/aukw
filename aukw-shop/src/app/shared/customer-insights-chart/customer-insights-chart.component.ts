import { Component, Input, SimpleChanges } from '@angular/core';
import Highcharts from 'highcharts/es-modules/masters/highstock.src.js'; // From https://github.com/highcharts/highcharts/issues/14183
import { CustomerInsightsChartData } from '@app/_models'; // defined in _models/chart_data.ts

@Component({
  selector: 'customer-insights-chart',
  imports: [],
  templateUrl: './customer-insights-chart.component.html',
  styleUrl: './customer-insights-chart.component.css',
})
export class CustomerInsightsChartComponent {
  @Input() movingAvgChartData?: CustomerInsightsChartData;

  private visibility: boolean[] = [true, false, false, false, false];

  public options: Highcharts.Options = {
    title: {
      text: 'Average Price Per Item',
    },
    subtitle: {
      text: 'Rolling 3 month average',
    },

    // rangeSelector is a property of stockChart only
    rangeSelector: {
      selected: 3, // 5yr
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
        text: 'Price (£0.00)',
      },
    },

    tooltip: {
      xDateFormat: '%A, %e-%b-%Y',
      shared: true,
      formatter: function () {
        return `The value for <b>${Highcharts.dateFormat(
          '%e %b %y',
          Number.parseFloat(this.key!.toString()),
        )}</b> is <b>£${Math.round(this.y! * 100) / 100}</b>`;
      },
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
      enabled: true,
      align: 'right',
      backgroundColor: '#FCFFC5',
      borderColor: 'black',
      borderWidth: 2,
      layout: 'vertical',
      verticalAlign: 'top',
      y: 100,
      shadow: true,
    },

    series: [
      {
        name: 'Clothing',
        data: [],
        type: 'line',
        color: '#800000',
        visible: this.visibility[0],
        showInLegend: this.visibility[0],
      },
      {
        name: 'Bric-a-brac',
        data: [],
        type: 'line',
        color: '#1cb2f5',
        visible: this.visibility[1],
        showInLegend: this.visibility[1],
      },
      {
        name: 'Books',
        data: [],
        type: 'line',
        visible: this.visibility[2],
        showInLegend: this.visibility[2],
      },
      {
        name: 'Linens',
        data: [],
        type: 'line',
        visible: this.visibility[3],
        showInLegend: this.visibility[3],
      },
      {
        name: 'All Departments',
        data: [],
        type: 'line',
        visible: this.visibility[4],
        showInLegend: this.visibility[4],
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
        (<any>this.options.series[0]).data =
          this.movingAvgChartData.clothing_avg_px;
        (<any>this.options.series[1]).data =
          this.movingAvgChartData.brica_avg_px;
        (<any>this.options.series[2]).data =
          this.movingAvgChartData.books_avg_px;
        (<any>this.options.series[3]).data =
          this.movingAvgChartData.linens_avg_px;
        (<any>this.options.series[4]).data =
          this.movingAvgChartData.alldepartments_avg_px;

        Highcharts.stockChart('moving-average', this.options); // using HighCharts Stock
      }
    }
  }

  showSeries(index_of_series: number) {
    if (this.options.series && this.movingAvgChartData) {
      for (let index = 0; index < 5; index++) {
        if (index == index_of_series) {
          this.options.series[index].visible = true;
          this.options.series[index].showInLegend = true;
        } else {
          this.options.series[index].visible = false;
          this.options.series[index].showInLegend = false;
        }
      }
      (<any>this.options.series[0]).data =
        this.movingAvgChartData.clothing_avg_px;
      (<any>this.options.series[1]).data = this.movingAvgChartData.brica_avg_px;
      (<any>this.options.series[2]).data = this.movingAvgChartData.books_avg_px;
      (<any>this.options.series[3]).data =
        this.movingAvgChartData.linens_avg_px;
      (<any>this.options.series[4]).data =
        this.movingAvgChartData.alldepartments_avg_px;

      Highcharts.stockChart('moving-average', this.options); // Re-draw
    }
  }
}
