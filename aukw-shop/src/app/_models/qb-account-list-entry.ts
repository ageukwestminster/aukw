/**
 * The name and id numbers of an employee
 */
export class QBAccountListEntry {
  /**
   * The transaction date
   */
  date: string;
  /**
   * The type of QB transaction. The only values seen to date are: 
   * Journal Entry, Bill, Transfer, Expense, Supplier Credit, Deposit, Cheque, Sales Receipt
   */
  type: ValueIdPair;
  /**
   * The label applied to the QB transaction
   */
  docnumber: string;
  /**
   * The type of QB transaction
   */
  emp_name: ValueIdPair;
  /**
   * Description of the QB transaction
   */
  memo: string;
  /**
   * The account of QB transaction
   */
  account: ValueIdPair;
  /**
   * The amount
   */
  amount: number | string;
  /**
   * A string showing the reconciled status of the transaction.
   */
  is_cleared: 'R' | 'C' | '';
  /**
   * The running total of the account
   */
  balance: number;

  constructor(obj?: any) {
    this.date = (obj && obj.date) || null;
    this.type = (obj && obj.type) || null;
    this.docnumber = (obj && obj.docnumber) || null;
    this.emp_name = (obj && obj.emp_name) || null;
    this.memo = (obj && obj.memo) || null;
    this.account = (obj && obj.account) || null;
    this.amount = (obj && obj.amount) || null;
    this.balance = (obj && obj.balance) || null;
    this.is_cleared = (obj && obj.is_cleared) || null;
  }

  public stringRepresentation() {
    return {
      date: this.date,
      type: this.type.value,
      docnumber: this.docnumber,
      emp_name: this.emp_name.value,
      memo: this.memo,
      account: this.account.value,
      amount: this.amount,
      is_cleared: this.is_cleared,
      balance: this.balance,
    };
  }
}

export class ValueIdPair {
  value: string;
  id: number;
  constructor(obj?: any) {
    this.value = (obj && obj.value) || null;
    this.id = (obj && obj.id) || null;
  }

  public toString(): string {
    return this.value;
  }
}
