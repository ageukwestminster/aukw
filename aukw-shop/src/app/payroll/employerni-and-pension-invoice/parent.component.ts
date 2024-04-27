import { Component, EventEmitter, inject, Input, Output } from '@angular/core';
import { EmployeeAllocation, IrisPayslip, LineItemDetail } from '@app/_models';
import {
  AlertService,
  LoadingIndicatorService,
  QBPayrollService,
  PayrollService,
} from '@app/_services';

@Component({
  standalone: true,
  imports: [],
  template: '',
})
export abstract class ParentComponent {
  lines: LineItemDetail[] = [];
  total: number = 0;

  @Input() allocations: EmployeeAllocation[] = [];
  @Input() payslips: IrisPayslip[] = [];
  @Input() payrollDate: string = '';
  @Output() onTransactionCreated = new EventEmitter();

  protected loadingIndicatorService = inject(LoadingIndicatorService);
  protected payrollService = inject(PayrollService);
  protected qbPayrollService = inject(QBPayrollService);
  protected alertService = inject(AlertService);
}
