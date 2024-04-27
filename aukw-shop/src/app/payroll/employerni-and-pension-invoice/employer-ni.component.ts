import { Component, OnChanges, SimpleChanges } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IrisPayslip, LineItemDetail } from '@app/_models';
import { scan, shareReplay, tap } from 'rxjs';
import { AllocatedCostsListComponent } from './allocated-costs-list/list.component';
import { ParentComponent } from './parent.component';

@Component({
  selector: 'employer-ni',
  standalone: true,
  imports: [AllocatedCostsListComponent, CommonModule],
  templateUrl: './employer-ni.component.html',
})
export class EmployerNiComponent extends ParentComponent implements OnChanges {
  /**
   * On every change of the input variables, recalculate the allocated employer ni costs.
   * @param changes SimpleChanges The inputs that have changed. Not used but retained to match interface.
   * @returns void
   */
  ngOnChanges(changes: SimpleChanges): void {
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
   * Create a single new journal entry in the Charity Quickbooks file that records the Employer NI amounts and
   * account and class allocations for each employee.
   */
  createTransaction() {
    // Filter out lines for which there is already a QBO entry
    const linesToAdd = this.lines.filter((item) => {
      this.payslips.filter(
        (p) =>
          p.payrollNumber == item.payrollNumber &&
          (!p.qbFlags || !p.qbFlags.shopJournal),
      ).length > 0;
    });
    if (linesToAdd && linesToAdd.length) {
      this.qbPayrollService
        .createEmployerNIJournal(linesToAdd, this.payrollDate)
        .pipe(
          this.loadingIndicatorService.createObserving({
            loading: () => 'Adding employer NI journal to Quickbooks',
            success: (result) =>
              `Successfully created journal with id=${result.id} in Quickbooks.`,
            error: (err) => `${err}`,
          }),
          shareReplay(1),
        )
        .subscribe();
    } else {
      this.alertService.info(
        'There are no entries to add: they are all in Quickbooks already.',
      );
    }
  }

  /** This is the property that the list must check to see iof the line is in QBO or not*/
  inQBOProperty() {
    return function (payslip: IrisPayslip): boolean {
      return payslip.qbFlags.employerNI;
    };
  }
}
