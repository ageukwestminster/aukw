/**
 * Data on sales and expenses that can be used to complete the CRA QMA report
 */
export class ProfitAndLossData {
  /** Title of the report */
  title: string;
  /** The period to which the values refer */
  range: PnlReportRange;
  income: PnLItem;
  cogs: PnLItem;
  grossprofit: PnLItem;
  expenses: PnLItem;
  netoperatingincome: PnLItem;
  otherincome: PnLItem;
  otherexpenses: PnLItem;
  netotherincome: PnLItem;
  netincome: PnLItem;

  constructor(obj?: any) {
    this.title = (obj && obj.title) || null;
    this.range = (obj && obj.range) || null;
    this.income = (obj && obj.income) || null;
    this.cogs = (obj && obj.cogs) || null;
    this.grossprofit = (obj && obj.grossprofit) || null;
    this.expenses = (obj && obj.expenses) || null;
    this.netoperatingincome = (obj && obj.netoperatingincome) || null;
    this.otherincome = (obj && obj.otherincome) || null;
    this.otherexpenses = (obj && obj.otherexpenses) || null;
    this.netotherincome = (obj && obj.netotherincome) || null;
    this.netincome = (obj && obj.netincome) || null;
  }
}
/**
 *
 */
export class PnlReportRange {
  currentPeriodStart: string;
  currentPeriodEnd: string;
  previousPeriodStart: string;
  previousPeriodEnd: string;
  constructor(obj?: any) {
    this.currentPeriodStart = (obj && obj.currentPeriodStart) || null;
    this.currentPeriodEnd = (obj && obj.currentPeriodEnd) || null;
    this.previousPeriodStart = (obj && obj.previousPeriodStart) || null;
    this.previousPeriodEnd = (obj && obj.previousPeriodEnd) || null;
  }
}

/**
 *
 */
export class PnlReportLineItem {
  displayName: string;
  currentValue: number;
  previousValue: number;
  constructor(obj?: any) {
    this.displayName = (obj && obj.displayName) || null;
    this.currentValue = (obj && obj.currentValue) || null;
    this.previousValue = (obj && obj.previousValue) || null;
  }
}

/**
 *
 */
export class PnLItem extends PnlReportLineItem {
  rows: PnlReportLineItem[];
  constructor(obj?: any) {
    super(obj);
    this.rows = (obj && obj.rows) || null;
  }
}
