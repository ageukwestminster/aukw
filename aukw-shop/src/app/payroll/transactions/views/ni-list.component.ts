import { Component, inject, OnInit } from '@angular/core';
import { AsyncPipe } from '@angular/common';
import { map, of, tap, scan, Observable } from 'rxjs';

import { IrisPayslip, LineItemDetail } from '@app/_models';
import { PayrollIdentifier } from '@app/_interfaces/payroll-identifier';
import { PayrollTransactionsService, QBPayrollService } from '@app/_services';
import { fromArrayToElement } from '@app/_helpers';
import { AllocatedCostsListComponent } from './allocated-costs-list/list.component';

@Component({
  standalone: true,
  imports: [AllocatedCostsListComponent, AsyncPipe],
  templateUrl: './ni-list.component.html',
  styleUrls: ['../shared.css'],
})
export class NILinesListComponent implements OnInit {
  lines: LineItemDetail[] = [];
  total: number = 0;
  payslips: Observable<IrisPayslip[]> = of([]);

  private payrollTransactionsService = inject(PayrollTransactionsService);
  private qbPayrollService = inject(QBPayrollService);

  ngOnInit() {
    this.payslips = this.qbPayrollService.payslips$;

    this.payrollTransactionsService.employerni$
      .pipe(
        tap((lines) => (this.lines = lines)),
        // Go from Observable<T[]> to Observable<T>
        fromArrayToElement(),

        // loop through all LineItemDetail's and sum the values to form a
        // "total" LineItemDetail that will be put in class level variable
        scan((prev: number, curr: LineItemDetail) => (prev += curr.amount), 0),
      )
      .subscribe((result) => (this.total = result));
  }

  inQBO(line: PayrollIdentifier): boolean {
    return this.payrollTransactionsService.inQBO(line, 'EmployerNI');
  }

  /** This is the property that the list must check to see if the line is in QBO or not*/
  getQBFlagsProperty() {
    return function (payslip: IrisPayslip) {
      return payslip.niJournalInQBO;
    };
  }
}
