import { Component } from '@angular/core';
import { Observable, of, toArray, shareReplay, tap } from 'rxjs';
import { IrisPayslip, LineItemDetail, PayrollProcessState } from '@app/_models';
import { BasePayrollTransactionComponent } from './base-transaction.component';

@Component({
  standalone: true,
  imports: [],
  template: '',
  selector: 'employer-ni',
})
export class EmployerNiComponent extends BasePayrollTransactionComponent<LineItemDetail> {
  total: number = 0;

  override createTransactions(): Observable<LineItemDetail[]> {
    if (!this.payslips.length || !this.allocations.length) return of([]);

    this.total = 0;
    this.lines = [];

    return this.payrollService
      .employerNIAllocatedCosts(this.payslips, this.allocations)
      .pipe(
        tap((line) => {
          this.lines.push(line);
          this.total += line.amount;
        }),
        toArray(),
      );
  }

  /**
   * Create a single new journal entry in the Charity QuickBooks file that records the Employer NI amounts and
   * account and class allocations for each employee.
   */
  addToQuickBooks() {
    // Filter out lines for which there is already a QBO entry
    const filteredTransactions = this.filteredTransactions(
      this.getQBFlagsProperty(),
    );

    // If lines have been found that match the above criteria then add to QBO
    if (filteredTransactions && filteredTransactions.length) {
      this.qbPayrollService
        .createEmployerNIJournal(filteredTransactions, this.payrollDate)
        .pipe(
          this.loadingIndicatorService.createObserving({
            loading: () => 'Adding employer NI journal to QuickBooks',
            success: (result) =>
              `Successfully created journal with id=${result.id} in QuickBooks.`,
            error: (err) => `${err}`,
          }),
          shareReplay(1),

          // Add entry to audit log
          tap((result) => {
            this.auditLogService.log(
              this.authenticationService.userValue,
              'INSERT',
              `Added employer NI journal with id=${result.id} to QuickBooks`,
              'General Journal',
              result.id,
            );
          }),
        )
        .subscribe({
          error: (e) => {
            this.alertService.error(e, { autoClose: false });
          },
          complete: () => {
            this.qbPayrollService.sendPayslips(this.setQBOFlagsToTrue());
            this.stateService.setState(PayrollProcessState.EMPLOYERNI);
          },
        });
    } else {
      this.alertService.info(
        'There are no Employer NI journal entries to add: they are all in QuickBooks already.',
      );
    }
  }

  /** This is the property that the list must check to see if the line is in QBO or not*/
  override getQBFlagsProperty() {
    return function (payslip: IrisPayslip) {
      return payslip.niJournalInQBO;
    };
  }
  override setQBFlagsProperty() {
    return function (payslip: IrisPayslip, value: boolean) {
      payslip.niJournalInQBO = value;
    };
  }
}
