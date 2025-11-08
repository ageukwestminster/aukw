import { Component, DestroyRef, inject, OnInit, Output } from '@angular/core';
import { reduce, Subject, takeUntil, tap } from 'rxjs';
import { PayslipListComponent } from './list/list.component';
import { PayslipsSummaryComponent } from './summary/payslips-summary.component';
import { QBPayrollService } from '@app/_services';
import { IrisPayslip } from '@app/_models';
import { fromArrayToElement } from '@app/_helpers';

@Component({
  selector: 'payslips',
  imports: [PayslipListComponent, PayslipsSummaryComponent],
  templateUrl: './payslips.component.html',
})
export class PayslipsComponent implements OnInit {
  payslips: IrisPayslip[] = [];
  total: IrisPayslip = new IrisPayslip();
  month: number = 0;

  private destroyRef = inject(DestroyRef);
  protected qbPayrollService = inject(QBPayrollService);

  ngOnInit(): void {
    const destroyed = new Subject();
    this.destroyRef.onDestroy(() => {
      destroyed.next('');
      destroyed.complete();
    });

    this.qbPayrollService.payslips$
      .pipe(
        takeUntil(destroyed),
        tap((response) => {
          this.payslips = response;

          // The number of the month: January is 0, February is 1,... December is 11
          if (response && response.length) {
            var monthNumber = new Date(response[0].payrollDate).getMonth();
            // Convert to fiscal month number
            this.month = monthNumber <= 2 ? monthNumber + 10 : monthNumber - 2;
          }
        }),
        fromArrayToElement(),
        reduce((prev: IrisPayslip, current) => {
          const cummTotal = (prev as IrisPayslip).add(current);
          return cummTotal;
        }, new IrisPayslip()),
      )
      .subscribe({
        // Create IrisPayslip!!!!
        next: (sum) => (this.total = sum),
        error: (err) => console.log(err),
      });
  }

  addEmployee(payslip: IrisPayslip) {}

  test() {
    console.log(this.month);
  }
}
