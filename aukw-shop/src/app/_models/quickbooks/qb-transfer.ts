import { QBAttachment } from '@app/_models';

/**
 * Defines the properties of a Transfer. This is a transaction between two accounts
 */
export class QBTransfer {
  id: number;
  readonly qbType: string = 'Transfer';
  txnDate: string;
  privateNote: string;
  amount: number;
  fromAccount: [number, string];
  toAccount: [number, string];
  attachments: QBAttachment[] = [];

  constructor(obj?: any) {
    this.id = (obj && obj.id) || null;
    this.txnDate = (obj && obj.txnDate) || null;
    this.privateNote = (obj && obj.privateNote) || null;
    this.amount = (obj && obj.amount) || null;
    this.fromAccount = (obj && obj.fromAccount) || null;
    this.toAccount = (obj && obj.toAccount) || null;
    this.attachments = (obj && obj.attachments) || null;
  }
}
