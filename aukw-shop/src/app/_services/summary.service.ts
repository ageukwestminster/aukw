import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { Summary } from '@app/_models';
import {
  DepartmentSalesChartData,
  MonthlySalesChartData,
  SalesChartData,
} from '@app/_models';

const baseUrl = `${environment.apiUrl}/report`;
const salesTabletUrl = baseUrl + `/summarytable`;
const salesChartUrl = baseUrl + `/sales-chart`;
const deptChartUrl = baseUrl + `/dept-chart`;
const monthlySalesChartUrl = baseUrl + `/monthly-sales`;

@Injectable({ providedIn: 'root' })
export class SummaryService {
  constructor(private http: HttpClient) {}

  getSummary() {
    return this.http.get<Summary[]>(salesTabletUrl);
  }

  getSalesChartData() {
    return this.http.get<SalesChartData>(salesChartUrl);
  }

  getDepartmentBreakdownChartData() {
    return this.http.get<DepartmentSalesChartData>(deptChartUrl);
  }

  getMonthlySalesChartData(shopid: number) {
    return this.http.get<MonthlySalesChartData[]>(
      monthlySalesChartUrl + `/${shopid}`,
    );
  }
}
