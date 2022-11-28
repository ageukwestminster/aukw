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

export class HistogramChartData {
  average: number;
  count: number;
  data: [number, number];

  constructor(obj?: any) {
    this.average = (obj && obj.average) || null;
    this.count = (obj && obj.count) || null;
    this.data = (obj && obj.data) || null;
  }
}
