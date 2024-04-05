export class EmployerNIEntry {
  employeeId: number;
  amount: number;
  account: number;
  class: number;

  constructor(obj?: any) {
    this.employeeId = (obj && obj.employeeId) || null;
    this.amount = (obj && obj.amount) || null;
    this.account = (obj && obj.account) || null;
    this.class = (obj && obj.class) || null;
  }
}
