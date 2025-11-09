import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';

import { NgbTooltip } from '@ng-bootstrap/ng-bootstrap';

import { PayrollJournalEntry } from '@app/_models';
import { PayrollIdentifier } from '@app/_interfaces/payroll-identifier';
import { PayrollTransactionsService } from '@app/_services';

@Component({
  selector: 'employee-journals',
  standalone: true,
  imports: [CommonModule, NgbTooltip],
  templateUrl: './employee-journals-list.component.html',
  styleUrls: ['../shared.css'],
})
export class EmployeeJournalsListComponent implements OnInit {
  lines: PayrollJournalEntry[] = [];

  private payrollTransactionsService = inject(PayrollTransactionsService);

  ngOnInit() {
    this.payrollTransactionsService.employeejournals$.subscribe(lines => this.lines = lines);
  }

  inQBO(line: PayrollIdentifier): boolean {
    return false;
  }
}
