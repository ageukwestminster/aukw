import { Component } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { Router } from '@angular/router';
import { DateRangeAdapter } from '@app/_helpers';
import {
  AlertService,
  ExportToCsvService,
  QBReportService,
} from '@app/_services';
import { AbstractChartReportComponent } from '../chart-report.component';
import { QBAccountListEntry } from '@app/_models/qb-account-list-entry';

@Component({
  selector: 'aukw-interco',
  templateUrl: './aukw-interco.component.html',
})
export class AukwIntercoComponent extends AbstractChartReportComponent<
  QBAccountListEntry[]
> {
  form!: FormGroup;
  enterprises: boolean = true;
  constructor(
    private alertService: AlertService,
    private reportService: QBReportService,
    private dateRangeAdapter1: DateRangeAdapter,
    private formBuilder1: FormBuilder,
    private router1: Router,
  ) {
    super(dateRangeAdapter1, formBuilder1, router1);
  }

  checkboxClick() {
    this.enterprises = !this.enterprises;
    this.onDateRangeChanged(this.f['dateRange'].value);
  }

  refreshSummary(startDate: string, endDate: string) {
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

  increaseAmount(amount: number | string): string {
    if (typeof amount === 'number' && amount > 0) {
      return String(amount);
    } else {
      return '';
    }
  }
  decreaseAmount(amount: number | string): string {
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
