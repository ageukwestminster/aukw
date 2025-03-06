import { QBAttachment } from './qb-attachment';

/**
 * Defines the properties of an Expense.
 */
export class QBExpense {
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
    this.attachments = (obj && obj.attachments) || null;
    this.taxAmount = (obj && obj.taxAmount) || null;
  }
}
