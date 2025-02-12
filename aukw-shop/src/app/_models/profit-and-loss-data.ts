/**
 * Data on sales and expenses that can be used to complete the CRA QMA report
 */
export class ProfitAndLossData {
  /** The period to which the values refer */
  period: string[];
  /**  */
  income: PnLItem;
  ragging: PnLItem;
  cogs: PnLItem;
  grossprofit: PnLItem;
  expenses: PnLItem;
  netoperatingincome: PnLItem;
  otherincome: PnLItem;
  donations: PnLItem;
  otherexpenses: PnLItem;
  netotherincome: PnLItem;
  netincome: PnLItem;

  constructor(obj?: any) {
    this.period = (obj && obj.period) || null;
    this.income = (obj && obj.income) || null;
    this.ragging = (obj && obj.ragging) || null;
    this.cogs = (obj && obj.cogs) || null;
    this.grossprofit = (obj && obj.grossprofit) || null;
    this.expenses = (obj && obj.expenses) || null;
    this.netoperatingincome = (obj && obj.netoperatingincome) || null;
    this.otherincome = (obj && obj.otherincome) || null;
    this.donations = (obj && obj.donations) || null;
    this.otherexpenses = (obj && obj.otherexpenses) || null;
    this.netotherincome = (obj && obj.netotherincome) || null;
    this.netincome = (obj && obj.netincome) || null;
  }
}

/**
 * 
 */
export class PnLItem {
  /** Tee period to which the values refer */
  total: number[];
  rows: PnlReportLineItem[];

  constructor(obj?: any) {
    this.total = (obj && obj.period) || null;
    this.rows = (obj && obj.rows) || null;
  }
}

/**
 * 
 */
export class PnlReportLineItem {
  name: string;
  value: number[];
  constructor(obj?: any) {
    this.name = (obj && obj.name) || null;
    this.value = (obj && obj.value) || null;
  }
}
