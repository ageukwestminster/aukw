/**
 * The name and id numbers of an employee
 */
export class QBAccountListEntry {
  /**
   * The transaction date
   */
  date: string;
  /**
   * The type of QB transaction
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
  }
}

export class ValueIdPair {
  value: string;
  id: number;
  constructor(obj?: any) {
    this.value = (obj && obj.value) || null;
    this.id = (obj && obj.id) || null;
  }
}
