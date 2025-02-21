import { Component, inject } from '@angular/core';
import { CommonModule, DatePipe } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import {
  NgbDatepickerModule,
  NgbDateAdapter,
  NgbDateParserFormatter,
} from '@ng-bootstrap/ng-bootstrap';
import { tap } from 'rxjs/operators';

import { environment } from '@environments/environment';

import { HistogramChartData } from '@app/_models';
import { ReportService } from '@app/_services';
import { AbstractChartReportComponent } from '../chart-report.component';
import { SalesHistogramChartComponent } from '@app/shared';
import { CustomDateParserFormatter, NgbUTCStringAdapter } from '@app/_helpers';
import { DateRangeChooserComponent } from '@app/shared';

@Component({
  templateUrl: './sales-histogram.component.html',
  standalone: true,
  imports: [
    CommonModule,
    NgbDatepickerModule,
    ReactiveFormsModule,
    RouterLink,
    SalesHistogramChartComponent,
    DateRangeChooserComponent,
  ],
  providers: [
    { provide: NgbDateAdapter, useClass: NgbUTCStringAdapter },
    { provide: NgbDateParserFormatter, useClass: CustomDateParserFormatter },
    DatePipe,
  ],
})
export class SalesHistogramComponent extends AbstractChartReportComponent<HistogramChartData> {
  private reportService = inject(ReportService);

  /** Convenience getter for easy access to form fields */
  get histogramChartData() {
    return this.data;
  }

  override refreshSummary(startDate: string, endDate: string) {
    this.reportService
      .getSalesHistogram(startDate, endDate, environment.HARROWROAD_SHOPID)
      .pipe(
        tap({
          next: (result) => {
            this.data = result;
          },
          error: (error) => {
            console.log(error);
          },
        }),
      )
      .subscribe();
  }

  onRowSelected(salesRow: [number, string, number]) {
    if (salesRow && salesRow[0]) {
      this.router.navigate([`takings/view/${salesRow[0]}`]);
    }
  }
}
