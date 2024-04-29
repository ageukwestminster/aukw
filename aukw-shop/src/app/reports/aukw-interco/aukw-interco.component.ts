import { Component, inject } from '@angular/core';
import { CommonModule, NgIf, NgClass } from '@angular/common';
import { FormGroup, ReactiveFormsModule } from '@angular/forms';
import {
  NgbAccordionModule,
  NgbDatepickerModule,
} from '@ng-bootstrap/ng-bootstrap';
import { RouterLink } from '@angular/router';
import { AlertService, QBReportService } from '@app/_services';
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
> {
  form!: FormGroup;
  enterprises: boolean = true;

  private alertService = inject(AlertService);
  private reportService = inject(QBReportService);

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
