/**
 * Stores the detail of a QBO entity 'Line'
 */
export class LineItemDetail {
  quickbooksId: number;
  name: string;
  amount: number;
  account: number;
  class: string;

  constructor(obj?: any) {
    this.quickbooksId = (obj && obj.quickbooksId) || 0;
    this.name = (obj && obj.name) || null;
    this.amount = (obj && obj.amount) || 0;
    this.account = (obj && obj.account) || null;
    this.class = (obj && obj.class) || null;
  }
}
