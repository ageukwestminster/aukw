/**
 * Details of an entry in an account. Taken from QBO.
 */
export class QBAccountListEntry {
  /**
   * Unique row id
   */
  id: number;
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
   * The name of the employee associated with the transaction. Not the person who entered it.
   */
  employee: ValueIdPair;
  /**
   * The name of the entity who is the counterparty to the transaction.
   */
  name: ValueIdPair;
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
  /**
   * 'True' if this transaction is taxable (i.e. VATable), false otherwise
   */
  taxable: boolean;

  constructor(obj?: any) {
    this.id = (obj && obj.id) || null;
    this.date = (obj && obj.date) || null;
    this.type = (obj && obj.type) || null;
    this.docnumber = (obj && obj.docnumber) || null;
    this.name = (obj && obj.name) || null;
    this.employee = (obj && obj.emp_name) || null;
    this.memo = (obj && obj.memo) || null;
    this.account = (obj && obj.account) || null;
    this.amount = (obj && obj.amount) || null;
    this.balance = (obj && obj.balance) || null;
    this.is_cleared = (obj && obj.is_cleared) || null;
    this.taxable = (obj && obj.taxable) || false;
  }

  public stringRepresentation() {
    return {
      id: this.id,
      date: this.date,
      type: this.type.value,
      docnumber: this.docnumber,
      name: this.name.value,
      employee: this.employee.value,
      memo: this.memo,
      account: this.account.value,
      amount: this.amount,
      is_cleared: this.is_cleared,
      taxable: this.taxable ? 'Yes' : 'No',
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

export class ValueIdType extends ValueIdPair {
  type: string;

  constructor(obj?: any) {
    super(obj);
    this.type = (obj && obj.type) || null;
  }
}
