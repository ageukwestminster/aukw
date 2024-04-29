import { Component, inject } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { NgbDatepickerModule } from '@ng-bootstrap/ng-bootstrap';
import { tap } from 'rxjs/operators';

import { environment } from '@environments/environment';

import { HistogramChartData } from '@app/_models';
import { ReportService } from '@app/_services';
import { AbstractChartReportComponent } from '../chart-report.component';
import { SharedModule } from '@app/shared/shared.module';

@Component({
  templateUrl: './sales-histogram.component.html',
  standalone: true,
  imports: [NgbDatepickerModule, ReactiveFormsModule, RouterLink, SharedModule],
})
export class SalesHistogramComponent extends AbstractChartReportComponent<HistogramChartData> {

  private reportService = inject(ReportService);

  /** Convenience getter for easy access to form fields */
  get histogramChartData() {
    return this.data;
  }

  refreshSummary(startDate: string, endDate: string) {
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
