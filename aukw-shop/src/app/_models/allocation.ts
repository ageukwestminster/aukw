
/**
 * Define the properties of an employee allocation
 */

export class EmployeeAllocation {
  id: number;
  name: string;
  allocations: Allocation[];

  constructor(obj?: any) {
    this.id = (obj && obj.id) || 0;
    this.name = (obj && obj.name) || null;
    this.allocations = (obj && obj.allocations) || null;
  }
}

export class Allocation {
  percentage: number;
  account: NameValuePair;
  class: NameValuePair;

  constructor(obj?: any) {
    this.percentage = (obj && obj.percentage) || 0;
    this.account = (obj && obj.account) || null;
    this.class = (obj && obj.class) || null;
  }
}

export class NameValuePair {
  value: number;
  name: string;

  constructor(obj?: any) {
    this.value = (obj && obj.value) || 0;
    this.name = (obj && obj.name) || null;
  }
}
