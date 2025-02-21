import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { 
  AvgDailyTransactionData,
  AvgDailyTransactionDataByQuarter,
  AvgWeeklySalesData,
  AvgWeeklySalesDataByQuarter,
  DepartmentSalesChartData,
  MonthlySalesChartData,
  SalesByDepartment,
  SalesChartData,
  HistogramChartData, 
  MovingAverageSalesChartData,
  Summary
} from '@app/_models';

const baseUrl = `${environment.apiUrl}/report`;
const salesTabletUrl = baseUrl + `/summarytable`;
const salesChartUrl = baseUrl + `/sales-chart`;
const deptChartUrl = baseUrl + `/dept-chart`;
const monthlySalesChartUrl = baseUrl + `/monthly-sales`;
const averageWeeklySalesUrl = baseUrl + `/avg-weekly-sales`;
const averageWeeklySalesByQUrl = baseUrl + `/avg-weekly-sales-by-quarter`;
const averageDailyTxnSizeUrl = baseUrl + `/avg-daily-transaction-size`;
const averageDailyTxnSizeByQUrl = baseUrl + `/avg-daily-txn-by-quarter`;
const salesByDeptUrl = baseUrl + `/sales-by-department`;

/**
 * Provides a set of methods to provide data for reports and charts. The data comes from 
 * the MariaDB database, not QuickBooks. For QBO reports use {@link QBReportService}.
 */
@Injectable({ providedIn: 'root' })
export class ReportService {
  constructor(private http: HttpClient) {}

  /**
   * Provide the data necessary to create the Histogram chart that appears on the Home page
   * @param start 
   * @param end 
   * @param shopid The id of the shop. Almost always equal to '1' for Harrow Road
   * @returns 
   */
  getSalesHistogram(start: string = '', end: string = '', shopid: number = 1) {
    return this.http.get<HistogramChartData>(
      `${baseUrl}/histogram?start=${start}&end=${end}&shopID=${shopid}`,
    );
  }

  /**
   * Provide the data necessary to create the Moving Average sales chart that appears on the Home page
   * @param start 
   * @param shopid The id of the shop. Almost always equal to '1' for Harrow Road
   * @returns 
   */
  getMovingAverageSales(start: string = '', shopid: number = 1) {
    return this.http.get<MovingAverageSalesChartData>(
      `${baseUrl}/moving-avg?start=${start}&shopID=${shopid}`,
    );
  }

  /**
   * 
   * @param start The start date of the report period in ISO 8601 format.
   * @param end The end date of the report period in ISO 8601 format.
   * @param shopid The id of the shop. Almost always equal to '1' for Harrow Road
   * @returns 
   */
  getSalesByDepartment(start: string, end: string, shopid: number = 1) {
    return this.http.get<SalesByDepartment>(
      salesByDeptUrl + `/${shopid}?start=${start}&end=${end}`,
    );
  }

  getSummary() {
    return this.http.get<Summary[]>(salesTabletUrl);
  }

  getSalesChartData() {
    return this.http.get<SalesChartData>(salesChartUrl);
  }

  /**
   * Get the average weekly sales, for a particular date period and shop.
   * @param start The start date of the report period in ISO 8601 format.
   * @param end The end date of the report period in ISO 8601 format.
   * @param shopid The id of the shop. Almost always equal to '1' for Harrow Road
   * @returns
   */
  getAverageWeeklySalesData(start: string, end: string, shopid: number = 1) {
    return this.http.get<AvgWeeklySalesData>(
      averageWeeklySalesUrl + `/${shopid}?start=${start}&end=${end}`,
    );
  }

    /**
   * Get the average weekly sales, organised by quarter for a shop
   * @param shopid The id of the shop. Almost always equal to '1' for Harrow Road
   * @returns Array of AvgWeeklySalesDataByQuarter objects
   */
    getAverageWeeklySalesByQuarter(shopid: number) {
      return this.http.get<AvgWeeklySalesDataByQuarter[]>(
        averageWeeklySalesByQUrl + `/${shopid}`,
      );
    }

  /**
   * Get the average daily transaction number and size, organised by quarter for a shop
   * @param shopid The id of the shop. Almost always equal to '1' for Harrow Road
   * @returns Array of AvgDailyTransactionDataByQuarter objects
   */
  getAvgDailyTransactionsByQuarter(shopid: number) {
    return this.http.get<AvgDailyTransactionDataByQuarter[]>(
      averageDailyTxnSizeByQUrl + `/${shopid}`,
    );
  }

    
  /**
   * Get the average daily transaction number and size, for a particular date period and shop.
   * @param start The start date of the report period in ISO 8601 format.
   * @param end The end date of the report period in ISO 8601 format.
   * @param shopid The id of the shop. Almost always equal to '1' for Harrow Road
   * @returns 
   */
  getAvgDailyTransactions(start: string, end: string, shopid: number = 1) {
    return this.http.get<AvgDailyTransactionData>(
      averageDailyTxnSizeUrl + `/${shopid}?start=${start}&end=${end}`,
    );
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
   *  supplied then the data will start from the 1st of the month
   * @returns Array of MonthlySalesChartData objects
   */
  getMonthlySalesChartData(
    shopid: number,
    yearOfStartDate?: number,
    monthOfStartDate?: number,
    dayOfStartDate?: number,
  ) {
    if (yearOfStartDate === undefined) {
      return this.http.get<MonthlySalesChartData[]>(
        monthlySalesChartUrl + `/${shopid}`,
      );
    } else if (monthOfStartDate === undefined) {
      return this.http.get<MonthlySalesChartData[]>(
        monthlySalesChartUrl +
          `/${shopid}/${yearOfStartDate}/${monthOfStartDate}`,
      );
    } else if (dayOfStartDate === undefined) {
      let month: string = monthOfStartDate.toLocaleString(undefined, {
        minimumIntegerDigits: 2,
      });
      return this.http.get<MonthlySalesChartData[]>(
        monthlySalesChartUrl + `/${shopid}/${yearOfStartDate}/${month}`,
      );
    } else {
      let month: string = monthOfStartDate.toLocaleString(undefined, {
        minimumIntegerDigits: 2,
      });
      let day: string = dayOfStartDate.toLocaleString(undefined, {
        minimumIntegerDigits: 2,
      });
      return this.http.get<MonthlySalesChartData[]>(
        monthlySalesChartUrl + `/${shopid}/${yearOfStartDate}/${month}/${day}`,
      );
    }
  }
}
