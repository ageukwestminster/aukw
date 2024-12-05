import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IrisPayslip, LineItemDetail, PayrollProcessState } from '@app/_models';
import { scan, shareReplay, tap } from 'rxjs';
import { AllocatedCostsListComponent } from './allocated-costs-list/list.component';
import { BasePayrollTransactionComponent } from '../parent.component';

@Component({
  standalone: true,
  imports: [AllocatedCostsListComponent, CommonModule],
  templateUrl: './employer-ni.component.html',
  selector: 'employer-ni',
})
export class EmployerNiComponent extends BasePayrollTransactionComponent<LineItemDetail> {
  total: number = 0;

  override recalculateTransactions() {
    if (!this.payslips.length || !this.allocations.length) return;

    this.total = 0;
    this.lines = [];

    this.payrollService
      .employerNIAllocatedCosts(this.payslips, this.allocations)
      .pipe(
        tap((line: LineItemDetail) => this.lines.push(line)),
        scan((a: number, v: LineItemDetail) => a + v.amount, 0),
      )
      .subscribe((total: number) => (this.total = total));
  }

  /**
   * Create a single new journal entry in the Charity QuickBooks file that records the Employer NI amounts and
   * account and class allocations for each employee.
   */
  createTransaction() {
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
              "INSERT",
              `Added employer NI journal with id=${result.id} to QuickBooks`,
              "General Journal",
              result.id
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
        'There are no entries to add: they are all in QuickBooks already.',
      );
    }
  }

  /** This is the property that the list must check to see if the line is in QBO or not*/
  override getQBFlagsProperty() {
    return function (payslip: IrisPayslip) {
      return payslip.qbFlags.employerNI;
    };
  }
  override setQBFlagsProperty() {
    return function (payslip: IrisPayslip, value: boolean) {
      payslip.qbFlags.employerNI = value;
    };
  }
}
