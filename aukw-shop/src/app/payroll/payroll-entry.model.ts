import {
  ApiMessage,
  EmployeeAllocation,
  IrisPayslip,
  LineItemDetail,
} from '@app/_models';
import { Observable } from 'rxjs';

export class PayrollEntryTypeDetails {
  title: string;
  transactionType: string;
  inQBOProperty: (p: IrisPayslip) => boolean;
  costAllocations: (
    p: IrisPayslip[],
    a: EmployeeAllocation[],
  ) => Observable<LineItemDetail>;
  transactionCreation: (
    l: LineItemDetail[],
    dt: string,
  ) => Observable<ApiMessage>;

  constructor(obj?: any) {
    this.title = (obj && obj.title) || null;
    this.transactionType = (obj && obj.transactionType) || null;
    this.inQBOProperty = (obj && obj.inQBOProperty) || null;
    this.costAllocations = (obj && obj.costAllocations) || null;
    this.transactionCreation = (obj && obj.allocationMethod) || null;
  }
}
