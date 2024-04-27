import { Component, OnChanges, SimpleChanges } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IrisPayslip, LineItemDetail } from '@app/_models';
import { scan, shareReplay, tap } from 'rxjs';
import { AllocatedCostsListComponent } from './allocated-costs-list/list.component';
import { ParentComponent } from './parent.component';

@Component({
  selector: 'pension-invoice',
  standalone: true,
  imports: [AllocatedCostsListComponent, CommonModule],
  templateUrl: './pension-invoice.component.html',
})
export class PensionInvoiceComponent
  extends ParentComponent
  implements OnChanges
{
  totalSalarySacrifice: number = 0;
  totalEmployeePension: number = 0;

  /**
   * On every change of the input variables, recalculate the allocated pension costs.
   * @param changes SimpleChanges The inputs that have changed. Not used but retained to match interface.
   * @returns void
   */
  ngOnChanges(changes: SimpleChanges): void {
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
    const linesToAdd = this.lines.filter((item) => {
      let ps = this.payslips.filter(
        (p) =>
          p.payrollNumber == item.payrollNumber &&
          (!p.qbFlags || !p.qbFlags.pensionBill),
      );
      return ps.length > 0;
    });

    //Add the invoice
    if (linesToAdd && linesToAdd.length) {
      this.qbPayrollService
        .createPensionBill(
          {
            salarySacrificeTotal: this.totalSalarySacrifice.toFixed(2),
            employeePensionTotal: this.totalEmployeePension.toFixed(2),
            pensionCosts: linesToAdd,
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
          error: (error: any) => {
            this.alertService.error(error, { autoClose: false });
          },
          complete: () => this.onTransactionCreated.emit(),
        });
    } else {
      this.alertService.info(
        'There are no entries to add: they are all in Quickbooks already.',
      );
    }
  }

  /** This is the property that the list must check to see if the line is in QBO or not*/
  inQBOProperty(): (p: IrisPayslip) => boolean {
    return function (payslip: IrisPayslip): boolean {
      return payslip.qbFlags.pensionBill;
    };
  }
}
