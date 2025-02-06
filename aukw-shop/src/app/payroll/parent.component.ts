import { Component, DestroyRef, inject, OnInit } from '@angular/core';
import { EmployeeAllocation, IrisPayslip, User } from '@app/_models';
import {
  AlertService,
  AuditLogService,
  AuthenticationService,
  LoadingIndicatorService,
  QBPayrollService,
  PayrollService,
  PayrollProcessStateService,
} from '@app/_services';
import { PayrollIdentifier } from '@app/_interfaces/payroll-identifier';
import { Subject, takeUntil, tap } from 'rxjs';

@Component({
  standalone: true,
  imports: [],
  template: '',
})
export abstract class BasePayrollTransactionComponent<
  T extends PayrollIdentifier,
> implements OnInit
{
  /**
   * An array of objects that will be used to create a tranaction
   * or transactions on QBO.
   * The objects extend the PayrollIdentifier interface.
   */
  lines: T[] = [];

  allocations: EmployeeAllocation[] = [];
  payslips: IrisPayslip[] = [];
  payrollDate: string = '';

  protected loadingIndicatorService = inject(LoadingIndicatorService);
  protected payrollService = inject(PayrollService);
  protected qbPayrollService = inject(QBPayrollService);
  protected alertService = inject(AlertService);
  private destroyRef = inject(DestroyRef);
  protected stateService = inject(PayrollProcessStateService);
  protected auditLogService = inject(AuditLogService);
  protected authenticationService = inject(AuthenticationService);

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

  /**
   * Convert the array of IrisPayslips stored in payslips[] and the array of
   * project allocations stored in allocations[] to an array of objects in lines[].
   * Later, the line[] object will be used to create QBO transactions.
   * 
   * This method is called in ngOnInit and nothing is returned (void).
   */
  recalculateTransactions() {}

  /**
   * Filter out lines for which there is already a QBO entry
   * @param prop This is a function that accepts an IrisPayslip as a parameter and returns a boolean result.
   * @returns An array of transactions
   */
  filteredTransactions(prop: (p: IrisPayslip) => boolean) {

    return this.lines.filter((item) => {
      let ps = this.payslips.filter(
        (p) =>
          p.payrollNumber == item.payrollNumber && (!p.qbFlags || !prop(p)),
      );
      return ps.length > 0;
    });
  }

  /**
   * Check if the values contained in the given LineItemDetail have been flagged as having already been
   * entered in QuickBooks.
   * @param line The details of the entry
   * @returns 'True' if already in QBO.
   */
  inQBO(line: PayrollIdentifier): boolean {
    if (!this.payslips || !this.payslips.length) return false;
    return (
      this.payslips.filter(
        (p) =>
          p.payrollNumber == line.payrollNumber && this.getQBFlagsProperty()(p),
      ).length != 0
    );
  }

  getQBFlagsProperty() {
    return function (payslip: IrisPayslip) {
      return false;
    };
  }
  setQBFlagsProperty() {
    return function (payslip: IrisPayslip, value: boolean) {};
  }
  setQBOFlagsToTrue() {
    for (const payslip of this.payslips) {
      this.setQBFlagsProperty()(payslip, true);
    }
    return this.payslips;
  }
}
