import { ProfitAndLossData, PnlReportLineItem } from "./profit-and-loss-data";

export class QMAReport extends ProfitAndLossData{
  ragging: PnlReportLineItem;
  donations: PnlReportLineItem;

  constructor(obj?: any) {
    super(obj);
    this.ragging = (obj && obj.ragging) || null;
    this.donations = (obj && obj.donations) || null;
  }
}

