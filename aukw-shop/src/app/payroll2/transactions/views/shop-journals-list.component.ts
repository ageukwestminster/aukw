import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';

import { NgbTooltip } from '@ng-bootstrap/ng-bootstrap';

import { LineItemDetail, PayrollJournalEntry } from '@app/_models';
import { PayrollIdentifier } from '@app/_interfaces/payroll-identifier';
import { PayrollTransactionsService } from '@app/_services';

@Component({
  selector: 'employee-journals',
  standalone: true,
  imports: [CommonModule, NgbTooltip],
  templateUrl: './shop-journal.component.html',
  styleUrls: ['../shared.css'],
})
export class ShopJournalsListComponent implements OnInit {
  lines: LineItemDetail[] = [];

  private payrollTransactionsService = inject(PayrollTransactionsService);

  ngOnInit() {
    this.payrollTransactionsService.enterprises$.subscribe(
      (lines) => (this.lines = lines),
    );
  }

  inQBO(line: PayrollIdentifier): boolean {
    return false;
  }
}
