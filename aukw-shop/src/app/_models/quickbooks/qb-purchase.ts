import { QBAttachment } from '@app/_models';

/**
 * Defines the properties of a QBO Purchase (aka an Expense).
 */
export class QBPurchase {
  id: number;
  readonly qbType: string = 'Purchase';
  txnDate: string;
  privateNote: string;
  description: string;
  amount: number;
  taxAmount: number;
  bankAccount: [number, string];
  expenseAccount: [number, string];
  entity: [number, string];
  attachments: QBAttachment[] = [];

  constructor(obj?: any) {
    this.id = (obj && obj.id) || null;
    this.txnDate = (obj && obj.txnDate) || null;
    this.privateNote = (obj && obj.privateNote) || null;
    this.description = (obj && obj.description) || null;
    this.amount = (obj && obj.amount) || null;
    this.bankAccount = (obj && obj.bankAccount) || null;
    this.expenseAccount = (obj && obj.expenseAccount) || null;
    this.entity = (obj && obj.entity) || null;
    this.attachments = (obj && obj.attachments) || [];
    this.taxAmount = (obj && obj.taxAmount) || null;
  }

  public toJson() {
    return {
      txnDate: this.txnDate,
      entity: this.entity[0],
      bankAccount: this.bankAccount[0],
      expenseAccount: this.expenseAccount[0],
      privateNote: this.privateNote,
      description: this.description,
      amount: this.amount,
      taxAmount: this.taxAmount,      
    };
  }
}
