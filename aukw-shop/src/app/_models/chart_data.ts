import { Takings } from './takings';

/**Stores data to display a line graph of most recent sales versus historical averages
 * The chart object is called 'sales-chart'
 */
export class SalesChartData {
  current_date: string;
  /**'1' for Harrow Road, '2' for Church Street (now closed) */
  shopid: number;
  /**The dates of the sales values */
  dates: [string];
  /** Each item array is made up of i) unix timestamp*1000 and ii) net sales.
   *  Timestamp is multiplied by 1000 because timestamps in Javascript are milliseconds */
  sales: [[number, number]];
  avg: [[number, number]];
  avg30: [[number, number]];
  avg365: [[number, number]];
  avgAll: [[number, number]];
  /**Database ID for each takings item */
  takingsids: [number];

  constructor(obj?: any) {
    this.current_date = (obj && obj.current_date) || null;
    this.shopid = (obj && obj.shopid) || null;
    this.dates = (obj && obj.dates) || null;
    this.sales = (obj && obj.sales) || null;
    this.avg = (obj && obj.avg) || null;
    this.avg30 = (obj && obj.avg30) || null;
    this.avg365 = (obj && obj.avg365) || null;
    this.avgAll = (obj && obj.avgAll) || null;
    this.takingsids = (obj && obj.takingsids) || null;
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
  /** i) unix timestamp*1000 of sales date and ii) net sales.
   *  Timestamp is multiplied by 1000 because timestamps in Javascript are milliseconds
   */
  data: [[number, number]];

  /**The most recent sales numbers within the data series */
  last: [number, string, number];

  /**A list of takings values with associated TakingsID and sorted by date.
   * The parameters are: TakingsID, Date, Value */
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

/**
 * Stores data to display a line graph of moving averages of sales
 * The chart object is  'shared/moving-avg-chart'
 */
export class MovingAverageSalesChartData {
  /**'1' for Harrow Road, '2' for Church Street (now closed) */
  shopid: number;
  /**The dates of the sales values */
  dates: [string];
  /** Each item array is made up of i) unix timestamp*1000 and ii) net sales.
   *  Timestamp is multiplied by 1000 because timestamps in Javascript are milliseconds */
  net_sales: [[number, number]];
  avg10: [[number, number]];
  avg20: [[number, number]];
  avgQuarter: [[number, number]];
  avgYear: [[number, number]];

  constructor(obj?: any) {
    this.shopid = (obj && obj.shopid) || null;
    this.dates = (obj && obj.dates) || null;
    this.net_sales = (obj && obj.net_sales) || null;
    this.avg10 = (obj && obj.avg10) || null;
    this.avg20 = (obj && obj.avg20) || null;
    this.avgQuarter = (obj && obj.avgQuarter) || null;
    this.avgYear = (obj && obj.avgYear) || null;
  }
}

/**Stores data to display a line graph of quarterly ragging
 * The chart object is called 'ragging-chart'
 */
export class RaggingChartData {
  /** Each item array is made up of i) unix timestamp*1000 and ii) net sales.
   *  Timestamp is multiplied by 1000 because timestamps in Javascript are milliseconds */
  books: [[number, number]];
  clothing: [[number, number]];
  household: [[number, number]];
  shoes: [[number, number]];
  other: [[number, number]];
  total: [[number, number]];

  constructor(obj?: any) {
    this.books = (obj && obj.books) || [];
    this.clothing = (obj && obj.clothing) || [];
    this.household = (obj && obj.household) || [];
    this.shoes = (obj && obj.shoes) || [];
    this.other = (obj && obj.other) || [];
    this.total = (obj && obj.total) || [];
  }
}

/**
 * Stores data to display a line graph of moving averages of cash Vs. credit card receipts
 */
export class CashRatioMovingAverageChartData {
  /**'1' for Harrow Road, '2' for Church Street (now closed) */
  shopid: number;
  /** Data starts on this day */
  start: string;
  /**The dates of the sales values */
  dates: [string];
  /** Each item array is made up of i) unix timestamp*1000 and ii) ratio cash/(cash+CC).
   *  Timestamp is multiplied by 1000 because timestamps in Javascript are milliseconds */
  ratio: [[number, number]];
  avg20: [[number, number]];
  avgQuarter: [[number, number]];

  constructor(obj?: any) {
    this.shopid = (obj && obj.shopid) || null;
    this.start = (obj && obj.shopid) || null;
    this.dates = (obj && obj.dates) || null;
    this.ratio = (obj && obj.ratio) || null;
    this.avg20 = (obj && obj.avg20) || null;
    this.avgQuarter = (obj && obj.avgQuarter) || null;
  }
}


