import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { HistogramChartData } from '@app/_models';

const baseUrl = `${environment.apiUrl}/report`;

@Injectable({ providedIn: 'root' })
export class ReportService {
  constructor(private http: HttpClient) {}

  /** Provide the data necessary to create the Histogram chart */
  getSalesHistogram(start: string = '', end: string = '', shopID: number = 1) {
    if (shopID == null) {
      shopID = 1;
    }

    return this.http.get<HistogramChartData>(
      `${baseUrl}/histogram?start=${start}&end=${end}&shopID=${shopID}`
    );
  }
}
