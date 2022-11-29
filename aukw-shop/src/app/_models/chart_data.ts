import { Takings } from './takings';

export class SalesChartData {
  date: string;
  sales: number;
  avg10: number;
  avg30: number;
  avg365: number;
  avgAll: number;

  constructor(obj?: any) {
    this.date = (obj && obj.date) || null;
    this.sales = (obj && obj.sales) || null;
    this.avg10 = (obj && obj.avg10) || null;
    this.avg30 = (obj && obj.avg30) || null;
    this.avg365 = (obj && obj.avg365) || null;
    this.avgAll = (obj && obj.avgAll) || null;
  }
}

export class DepartmentSalesChartData {
  WTD: Takings;
  MTD: Takings;
  YTD: Takings;
  TrYTD: Takings;

  constructor(obj?: any) {
    this.WTD = (obj && obj.WTD) || null;
    this.MTD = (obj && obj.MTD) || null;
    this.YTD = (obj && obj.YTD) || null;
    this.TrYTD = (obj && obj.TrYTD) || null;
  }
}

export class MonthlySalesChartData {
  shopid: number;
  start_date: string;
  month: number;
  year: number;
  count: number;
  sales: number;
  avg_sales: number;
  avg_clothing: number;
  avg_brica: number;
  avg_books: number;
  avg_linens: number;

  constructor(obj?: any) {
    this.shopid = (obj && obj.shopid) || null;
    this.start_date = (obj && obj.start_date) || null;
    this.month = (obj && obj.month) || null;
    this.year = (obj && obj.year) || null;
    this.count = (obj && obj.count) || null;
    this.sales = (obj && obj.sales) || null;
    this.avg_sales = (obj && obj.avg_sales) || null;
    this.avg_clothing = (obj && obj.avg_clothing) || null;
    this.avg_brica = (obj && obj.avg_brica) || null;
    this.avg_books = (obj && obj.avg_books) || null;
    this.avg_linens = (obj && obj.avg_linens) || null;
  }
}

/**Stores data to build a histogram of net daily sales data */
export class HistogramChartData {
  /**First day of period */
  start: string;
  /**Last day of period */
  end: string;
  /**'1' for Harrow Road, '2' for Church Street (now closed) */
  shopid: number;
  average: number;
  count: number;
  /** An array of arrays. Each item array is made up of i) unix timestamp*1000 and ii) net sales.
   *  Timestamp from MariaDB is multiplied by 1000 because timestamps in Javascript are milliseconds
   */
  data: [[number, number]];
  /**The most recent sales numbers within the data series */
  last: [number, string, number];
  list: [[number, string, number]];

  constructor(obj?: any) {
    this.start = (obj && obj.start) || null;
    this.end = (obj && obj.end) || null;
    this.shopid = (obj && obj.shopid) || null;
    this.average = (obj && obj.average) || null;
    this.count = (obj && obj.count) || null;
    this.data = (obj && obj.data) || null;
    this.last = (obj && obj.last) || null;
    this.list = (obj && obj.list) || null;
  }
}
