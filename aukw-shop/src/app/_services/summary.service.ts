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

  /**
   * Get the monthly sales data for a shop, optionally starting from a particular date
   * @param shopid The id of the shop. Almost always equal to '1' for Harrow Road
   * @param yearOfStartDate The year of the start date of the numbers. e.g. 2020. If not supplied the data will
   *  start from April 2021. The year must be in 4 digit numerical format.
   * @param monthOfStartDate The month of the start date of the numbers. e.g. 7 for July. If not supplied then
   *  the data will start from January of the yearOfStartDate
   * @param dayOfStartDate The day of the start date of the numbers. e.g. 15 fot the 15tyh of the month. If not
   *  supplied then the stat wil lstart from the 1st of the month 
   * @returns Array of MonthlySalesChartData objects
   */
  getMonthlySalesChartData(shopid: number, yearOfStartDate?: number, monthOfStartDate?: number, dayOfStartDate?: number) {
    if (yearOfStartDate === undefined) {
      return this.http.get<MonthlySalesChartData[]>(
        monthlySalesChartUrl + `/${shopid}`,
      );
    } else if (monthOfStartDate === undefined){
      return this.http.get<MonthlySalesChartData[]>(
        monthlySalesChartUrl + `/${shopid}/${yearOfStartDate}/${monthOfStartDate}`,
      );
    } else if (dayOfStartDate === undefined) {
      let month:string = (monthOfStartDate).toLocaleString(undefined, {minimumIntegerDigits: 2});
      return this.http.get<MonthlySalesChartData[]>(
        monthlySalesChartUrl + `/${shopid}/${yearOfStartDate}/${month}`,
      );
    } else {
      let month:string = (monthOfStartDate).toLocaleString(undefined, {minimumIntegerDigits: 2});
      let day:string = (dayOfStartDate).toLocaleString(undefined, {minimumIntegerDigits: 2});
      return this.http.get<MonthlySalesChartData[]>(
        monthlySalesChartUrl + `/${shopid}/${yearOfStartDate}/${month}/${day}`,
      );
    }

    return this.http.get<MonthlySalesChartData[]>(
      monthlySalesChartUrl + `/${shopid}`,
    );
  }
}
