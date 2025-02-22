import { PnlReportLineItem, PnlReportRange } from './profit-and-loss-data';

export class AvgDailyTransactionDataByQuarter {
  shopid: number;
  year: number;
  quarter: number;
  quarter_start: string;
  trading_year: number;
  trading_quarter: number;
  trading_days_in_quarter: number;
  avg_daily_transactions: number;
  sales_per_txn: number;

  constructor(obj?: any) {
    this.shopid = (obj && obj.shopid) || null;
    this.year = (obj && obj.year) || null;
    this.quarter = (obj && obj.quarter) || null;
    this.quarter_start = (obj && obj.quarter_start) || null;
    this.trading_year = (obj && obj.trading_year) || null;
    this.trading_quarter = (obj && obj.trading_quarter) || null;
    this.trading_days_in_quarter = (obj && obj.trading_days_in_quarter) || null;
    this.avg_daily_transactions = (obj && obj.avg_daily_transactions) || null;
    this.sales_per_txn = (obj && obj.sales_per_txn) || null;
  }
}

/**
 * Data on sales and expenses that can be used to complete the CRA QMA report
 */
export class AvgDailyTransactionData {
  /** Title of the report */
  title: string;
  shopid: number;
  /** The period to which the values refer */
  range: PnlReportRange;
  avg_daily_transactions: PnlReportLineItem;
  sales_per_txn: PnlReportLineItem;
  trading_days_in_period: PnlReportLineItem;
  computed_total: PnlReportLineItem;
  actual_total: PnlReportLineItem;

  constructor(obj?: any) {
    this.title = (obj && obj.title) || null;
    this.range = (obj && obj.range) || null;
    this.shopid = (obj && obj.shopid) || null;
    this.avg_daily_transactions = (obj && obj.avg_daily_transactions) || null;
    this.sales_per_txn = (obj && obj.sales_per_txn) || null;
    this.trading_days_in_period = (obj && obj.trading_days_in_period) || null;
    this.computed_total = (obj && obj.computed_total) || null;
    this.actual_total = (obj && obj.actual_total) || null;
  }
}
