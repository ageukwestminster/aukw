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
