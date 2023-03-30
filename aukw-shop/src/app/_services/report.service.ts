import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { HistogramChartData, MovingAverageSalesChartData } from '@app/_models';

const baseUrl = `${environment.apiUrl}/report`;

@Injectable({ providedIn: 'root' })
export class ReportService {
  constructor(private http: HttpClient) {}

  /** Provide the data necessary to create the Histogram chart */
  getSalesHistogram(start: string = '', end: string = '', shopID: number = 1) {
    return this.http.get<HistogramChartData>(
      `${baseUrl}/histogram?start=${start}&end=${end}&shopID=${shopID}`
    );
  }

  /** Provide the data necessary to create the Moving Average sales chart */
  getMovingAverageSales(start: string = '', shopID: number = 1) {
    return this.http.get<MovingAverageSalesChartData>(
      `${baseUrl}/moving-avg?start=${start}&shopID=${shopID}`
    );
  }
}
