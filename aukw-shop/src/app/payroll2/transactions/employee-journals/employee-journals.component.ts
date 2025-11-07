import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import {
  IrisPayslip,
  PayrollJournalEntry,
  PayrollProcessState,
} from '@app/_models';
import { from, mergeMap, shareReplay, tap, toArray } from 'rxjs';
import { NgbTooltip } from '@ng-bootstrap/ng-bootstrap';
import { BasePayrollTransactionComponent } from '../base-transaction.component';

@Component({
  selector: 'employee-journals',
  standalone: true,
  imports: [CommonModule, NgbTooltip],
  templateUrl: './employee-journals.component.html',
  styleUrls: ['./employee-journals.component.css', '../shared.css'],
})
export class EmployeeJournalsComponent extends BasePayrollTransactionComponent<PayrollJournalEntry> {
  override recalculateTransactions() {
    if (!this.payslips.length) return;

    this.payrollService
      .employeeJournalEntries(this.payslips, this.allocations)
      .pipe(toArray())
      .subscribe({
        next: (response) => (this.lines = response),
        error: (e) => {
          this.alertService.error(e, { autoClose: false });
        },
      });
  }

  /**
   * Create a set of new journals in the Charity QuickBooks file that records the salary,
   * deductions and net pay amounts for each employee.
   */
  createTransaction() {
    // Filter out lines for which there is already a QBO entry
    const filteredTransactions = this.filteredTransactions(
      this.getQBFlagsProperty(),
    );

    if (filteredTransactions && filteredTransactions.length) {
      from(filteredTransactions)
        .pipe(
          mergeMap((prospectivePayrollJournal) =>
            this.qbPayrollService.createEmployeeJournal(
              prospectivePayrollJournal,
              this.payrollDate,
            ),
          ),
          tap((result) => {
            this.auditLogService.log(
              this.authenticationService.userValue,
              'INSERT',
              `Added employee payslip journal with id=${result.id} to QuickBooks`,
              'General Journal',
              result.id,
            );
          }),
          toArray(),
          this.loadingIndicatorService.createObserving({
            loading: () => `Adding employee journals to Charity QuickBooks`,
            success: (result) =>
              `Successfully created ${result.length} journals in QuickBooks.`,
            error: (err) => `${err}`,
          }),
          shareReplay(1),
        )
        .subscribe({
          error: (e) => {
            this.alertService.error(e, { autoClose: false });
          },
          complete: () => {
            this.qbPayrollService.sendPayslips(this.setQBOFlagsToTrue());
            this.stateService.setState(PayrollProcessState.JOURNALS);
          },
        });
    } else {
      this.alertService.info(
        'There are no entries to add: they are all in QuickBooks already.',
      );
    }
  }

  override getQBFlagsProperty() {
    return function (payslip: IrisPayslip) {
      return payslip.qbFlags.employeeJournal;
    };
  }
  override setQBFlagsProperty() {
    return function (payslip: IrisPayslip, value: boolean) {
      payslip.qbFlags.employeeJournal = value;
    };
  }
}
