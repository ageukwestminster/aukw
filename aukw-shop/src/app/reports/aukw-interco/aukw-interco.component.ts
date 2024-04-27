import { Component } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { Router } from '@angular/router';

import { tap } from 'rxjs/operators';

import { DateRangeAdapter } from '@app/_helpers';
import { AlertService, QBReportService } from '@app/_services';
import { AbstractChartReportComponent } from '../chart-report.component';
import { QBAccountListEntry } from '@app/_models/qb-account-list-entry';

@Component({
  selector: 'aukw-interco',
  templateUrl: './aukw-interco.component.html',
})
export class AukwIntercoComponent extends AbstractChartReportComponent<QBAccountListEntry[]> {
  form!: FormGroup;
  constructor(
    private alertService: AlertService,
    private reportService: QBReportService,
    private dateRangeAdapter1: DateRangeAdapter,
    private formBuilder1: FormBuilder,
    private router1: Router,
  ) {
    super(dateRangeAdapter1, formBuilder1, router1);
  }

  refreshSummary(startDate: string, endDate: string) {
    this.reportService
      .getIntercoAccountLedger(startDate, endDate)
      .subscribe({
        next: (response) => this.data = response,
        error: (error: any) => {
          this.alertService.error(error, { autoClose: false });
        },
      });
  }

  increaseAmount(amount: number|string): string {
    if (typeof amount === 'number' && amount > 0) {
      return String(amount);
    } else {
      return '';
    }
  }
  decreaseAmount(amount: number|string): string {
    if (typeof amount === 'number' && amount <= 0) {
      return String(-amount);
    } else {
      return '';
    }
  }
}
