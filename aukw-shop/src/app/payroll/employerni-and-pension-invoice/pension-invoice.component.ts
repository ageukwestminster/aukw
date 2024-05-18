import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IrisPayslip, LineItemDetail, PayrollProcessState } from '@app/_models';
import { scan, shareReplay, tap } from 'rxjs';
import { AllocatedCostsListComponent } from './allocated-costs-list/list.component';
import { BasePayrollTransactionComponent } from '../parent.component';

@Component({
  standalone: true,
  imports: [AllocatedCostsListComponent, CommonModule],
  templateUrl: './pension-invoice.component.html',
  selector: 'pensions-invoice',
})
export class PensionInvoiceComponent extends BasePayrollTransactionComponent<LineItemDetail> {
  total: number = 0;
  totalSalarySacrifice: number = 0;
  totalEmployeePension: number = 0;

  override recalculateTransactions() {
    if (!this.payslips.length || !this.allocations.length) return;

    this.lines = [];
    this.payslips.forEach((p: IrisPayslip) => {
      this.totalSalarySacrifice += p.salarySacrifice;
      this.totalEmployeePension += p.employeePension;
    });
    this.lines.push(
      new LineItemDetail({
        payrollNumber: 0,
        name: 'Salary Sacrifice total',
        amount: this.totalSalarySacrifice,
        className: '04 Administration',
      }),
    );
    this.lines.push(
      new LineItemDetail({
        payrollNumber: 0,
        name: 'Employee Pension total',
        amount: this.totalEmployeePension,
        className: '04 Administration',
      }),
    );

    this.payrollService
      .pensionAllocatedCosts(this.payslips, this.allocations)
      .pipe(
        tap((line: LineItemDetail) => this.lines.push(line)),
        scan((a: number, v: LineItemDetail) => a + v.amount, 0),
      )
      .subscribe((total: number) => (this.total = total));
  }

  /**
   * Create a single new invoice in the Charity Quickbooks file that records the pension amounts and
   * account and class allocations for each employee.
   */
  createTransaction() {
    // Filter out lines for which there is already a QBO entry
    const filteredTransactions = this.filteredTransactions(this.getQBFlagsProperty())

    //Add the invoice
    if (filteredTransactions && filteredTransactions.length) {
      this.qbPayrollService
        .createPensionBill(
          {
            salarySacrificeTotal: this.totalSalarySacrifice.toFixed(2),
            employeePensionTotal: this.totalEmployeePension.toFixed(2),
            pensionCosts: filteredTransactions,
            total: (
              this.totalEmployeePension +
              this.totalSalarySacrifice +
              this.total
            ).toFixed(2),
          },
          this.payrollDate,
        )
        .pipe(
          this.loadingIndicatorService.createObserving({
            loading: () => `Adding pension invoice to Quickbooks`,
            success: (result) =>
              `Successfully created pension invoice with id=${result.id} in Quickbooks.`,
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
            this.stateService.setState(PayrollProcessState.PENSIONS);
          },
        });
    } else {
      this.alertService.info(
        'There are no entries to add: they are all in Quickbooks already.',
      );
    }
  }

  /** This is the property that the list must check to see if the line is in QBO or not*/
  override getQBFlagsProperty() {
    return function (payslip: IrisPayslip) {
      return payslip.qbFlags.pensionBill;
    };
  }
  override setQBFlagsProperty() {
    return function (payslip: IrisPayslip, value: boolean) {
      payslip.qbFlags.pensionBill = value;
    };
  }
}
