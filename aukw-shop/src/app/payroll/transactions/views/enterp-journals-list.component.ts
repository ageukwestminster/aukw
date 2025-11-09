import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { tap, scan } from 'rxjs';
import { NgbTooltip } from '@ng-bootstrap/ng-bootstrap';

import { IrisPayslip } from '@app/_models';
import { PayrollIdentifier } from '@app/_interfaces/payroll-identifier';
import { PayrollTransactionsService } from '@app/_services';
import { fromArrayToElement } from '@app/_helpers';

@Component({
  standalone: true,
  imports: [CommonModule, NgbTooltip],
  templateUrl: './enterp-journal.component.html',
  styleUrls: ['../shared.css'],
})
export class EnterprisesJournalsListComponent implements OnInit {
  lines: IrisPayslip[] = [];
  total: IrisPayslip = new IrisPayslip();

  private payrollTransactionsService = inject(PayrollTransactionsService);

  ngOnInit() {
    this.payrollTransactionsService.enterprises$
      .pipe(
        tap((lines) => (this.lines = lines)),
        // Go from Observable<PayrollJournalEntry[]> to Observable<PayrollJournalEntry>
        fromArrayToElement(),

        // loop through all PayrollJournalEntrys and sum the values to form a
        // "total" PayrollJournalEntry that will be put in class level variable
        scan((prev: IrisPayslip, current) => {
          return prev.add(current);
        }, new IrisPayslip()),
      )
      .subscribe((result) => (this.total = result));
  }

  inQBO(line: PayrollIdentifier): boolean {
    return this.payrollTransactionsService.inQBO(line, 'Enterprises');
  }
}
