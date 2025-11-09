import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { tap, scan } from 'rxjs';
import { NgbTooltip } from '@ng-bootstrap/ng-bootstrap';

import { PayrollJournalEntry } from '@app/_models';
import { PayrollIdentifier } from '@app/_interfaces/payroll-identifier';
import { PayrollTransactionsService } from '@app/_services';
import { fromArrayToElement } from '@app/_helpers';

@Component({
  selector: 'employee-journals',
  standalone: true,
  imports: [CommonModule, NgbTooltip],
  templateUrl: './employee-journals-list.component.html',
  styleUrls: ['../shared.css'],
})
export class EmployeeJournalsListComponent implements OnInit {
  lines: PayrollJournalEntry[] = [];
  total: PayrollJournalEntry = new PayrollJournalEntry();

  private payrollTransactionsService = inject(PayrollTransactionsService);

  ngOnInit() {
    this.payrollTransactionsService.employeejournals$
      .pipe(
        tap((lines) => (this.lines = lines)),
        // Go from Observable<PayrollJournalEntry[]> to Observable<PayrollJournalEntry>
        fromArrayToElement(),

        // loop through all PayrollJournalEntrys and sum the values to form a
        // "total" PayrollJournalEntry that will be put in class level variable
        scan((prev: PayrollJournalEntry, current) => {
          return prev.add(current);
        }, new PayrollJournalEntry()),
      )
      .subscribe((result) => (this.total = result));
  }

  inQBO(line: PayrollIdentifier): boolean {
    return this.payrollTransactionsService.inQBOEmployeeJournals(line);
  }
}
