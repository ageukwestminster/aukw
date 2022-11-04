import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { Summary } from '@app/_models';
import { DepartmentSalesChartData, SalesChartData } from '@app/_models';

const baseUrl = `${environment.apiUrl}/summary`;
const salesChartUrl = baseUrl + `/sales-chart`;
const deptChartUrl = baseUrl + `/dept-chart`;

@Injectable({ providedIn: 'root' })
export class SummaryService {
  constructor(private http: HttpClient) {}

  getSummary() {
    return this.http.get<Summary[]>(baseUrl);
  }

  getSalesChartData() {
    return this.http.get<SalesChartData[]>(salesChartUrl);
  }

  getDepartmentBreakdownChartData() {
    return this.http.get<DepartmentSalesChartData>(deptChartUrl);
  }
}
