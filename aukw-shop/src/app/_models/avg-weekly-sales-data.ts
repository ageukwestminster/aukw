import {PnlReportLineItem, PnlReportRange } from "./profit-and-loss-data";

export class AvgWeeklySalesDataByQuarter {
  shopid: number;
  year: number;
  quarter: number;
  quarter_start: string;
  trading_year: number;
  trading_quarter: number;
  weeks_in_quarter: number;
  avg_weekly_income: number;

  constructor(obj?: any) {
    this.shopid = (obj && obj.shopid) || null;
    this.year = (obj && obj.year) || null;
    this.quarter = (obj && obj.quarter) || null;
    this.quarter_start = (obj && obj.quarter_start) || null;
    this.trading_year = (obj && obj.trading_year) || null;
    this.trading_quarter = (obj && obj.trading_quarter) || null;
    this.weeks_in_quarter = (obj && obj.weeks_in_quarter) || null;
    this.avg_weekly_income = (obj && obj.avg_weekly_income) || null;
  }
}

/**
 * Data on weekly sales that can be used to complete the CRA QMA report
 */
export class AvgWeeklySalesData {
  /** Title of the report */
  title: string;
  shopid: number;
  /** The period to which the values refer */
  range: PnlReportRange;  
  avg_weekly_sales: PnlReportLineItem;
  week_count: PnlReportLineItem;
  trading_days_in_period: PnlReportLineItem;
  computed_total: PnlReportLineItem;
  actual_total: PnlReportLineItem;

  constructor(obj?: any) {
    this.title = (obj && obj.title) || null;
    this.range = (obj && obj.range) || null;
    this.shopid = (obj && obj.shopid) || null;
    this.avg_weekly_sales = (obj && obj.avg_weekly_sales) || null;
    this.week_count = (obj && obj.week_count) || null;
    this.trading_days_in_period = (obj && obj.trading_days_in_period) || null;
    this.computed_total = (obj && obj.computed_total) || null;
    this.actual_total = (obj && obj.actual_total) || null;
  }
}