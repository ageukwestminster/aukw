import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, NgIf, NgClass } from '@angular/common';
import { FormGroup, ReactiveFormsModule } from '@angular/forms';
import {
  NgbAccordionModule,
  NgbDatepickerModule,
} from '@ng-bootstrap/ng-bootstrap';
import { RouterLink } from '@angular/router';
import { AlertService, QBReportService } from '@app/_services';
import { DateRangeEnum } from '@app/_models';
import { AbstractChartReportComponent } from '../chart-report.component';
import { QBAccountListEntry } from '@app/_models/qb-account-list-entry';

@Component({
  templateUrl: './aukw-interco.component.html',
  standalone: true,
  imports: [
    CommonModule,
    NgbAccordionModule,
    NgbDatepickerModule,
    NgIf,
    RouterLink,
    ReactiveFormsModule,
  ],
})
export class AukwIntercoComponent extends AbstractChartReportComponent<
  QBAccountListEntry[]
> implements OnInit {
  enterprises: boolean = true;

  private alertService = inject(AlertService);
  private reportService = inject(QBReportService);

  /**
   * Override the default constructor ont he base class because
   * I want to use a non-default date range, namely 'Last 6 Months'.
   */
  override ngOnInit() {
    this.form = this.formBuilder.group({
      dateRange: [DateRangeEnum.LAST_SIX_MONTHS],
      startDate: [null],
      endDate: [null],
    });

    this.onDateRangeChanged(DateRangeEnum.LAST_SIX_MONTHS);
}

  checkboxClick() {
    this.enterprises = !this.enterprises;
    this.onDateRangeChanged(this.f['dateRange'].value);
  }

  override refreshSummary(startDate: string, endDate: string) {
    this.loading = true;
    this.reportService
      .getIntercoAccountLedger(startDate, endDate, this.enterprises)
      .subscribe({
        next: (response) => (this.data = response),
        error: (error: any) => {
          this.loading = false;
          this.alertService.error(error, { autoClose: false });
        },
        complete: () => (this.loading = false),
      });
  }

  formatPositiveNumber(amount: number | string): string {
    if (typeof amount === 'number' && amount > 0) {
      return String(amount);
    } else {
      return '';
    }
  }
  formatNegativeNumber(amount: number | string): string {
    if (typeof amount === 'number' && amount <= 0) {
      return String(-amount);
    } else {
      return '';
    }
  }

  override exportToCSV(): void {
    const output = new Array<any>();
    this.data.map((item) => {
      let dataInstance = Object.assign(new QBAccountListEntry(), item);
      output.push(dataInstance.stringRepresentation());
    });

    this.exportToCsvService.exportToCSV(output);
  }
}
