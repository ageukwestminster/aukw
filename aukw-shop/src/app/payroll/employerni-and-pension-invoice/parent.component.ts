import { Component, DestroyRef, EventEmitter, inject, OnInit, Output } from '@angular/core';
import { EmployeeAllocation, IrisPayslip, LineItemDetail } from '@app/_models';
import {
  AlertService,
  LoadingIndicatorService,
  QBPayrollService,
  PayrollService,
} from '@app/_services';
import { Subject, takeUntil, tap } from 'rxjs';

@Component({
  standalone: true,
  imports: [],
  template: '',
})
export abstract class ParentComponent implements OnInit {
  lines: LineItemDetail[] = [];
  total: number = 0;

  allocations: EmployeeAllocation[] = [];
  payslips: IrisPayslip[] = [];
  payrollDate: string = '';
  @Output() onTransactionCreated = new EventEmitter();

  protected loadingIndicatorService = inject(LoadingIndicatorService);
  protected payrollService = inject(PayrollService);
  protected qbPayrollService = inject(QBPayrollService);
  protected alertService = inject(AlertService);
  private destroyRef = inject(DestroyRef);

  ngOnInit() {
    const destroyed = new Subject();
    this.destroyRef.onDestroy(() => {
      destroyed.next('');
      destroyed.complete();
    });

    this.qbPayrollService.allocations$
      .pipe(takeUntil(destroyed))
      .subscribe((response) => (this.allocations = response));

    this.qbPayrollService.payslips$
      .pipe(
        takeUntil(destroyed),
        tap((response) => {
          this.payslips = response;
          this.payrollDate = response[0].payrollDate;
        }),
      )
      .subscribe(() => {
        this.recalculateTransactions();
      });
  }

  recalculateTransactions() {
    
  }
}
