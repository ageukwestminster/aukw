import { Component, inject } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { Router } from '@angular/router';

import { tap } from 'rxjs/operators';

import { environment } from '@environments/environment';

import { DateRangeAdapter } from '@app/_helpers';
import { HistogramChartData } from '@app/_models';
import { ReportService } from '@app/_services';
import { AbstractChartReportComponent } from '../chart-report.component';

@Component({
  selector: 'app-sales-histogram',
  templateUrl: './sales-histogram.component.html',
})
export class SalesHistogramComponent extends AbstractChartReportComponent<HistogramChartData> {
  constructor(
    private reportService: ReportService,
    private dateRangeAdapter1: DateRangeAdapter,
    private formBuilder1: FormBuilder,
    private router1: Router,
  ) {
    super(dateRangeAdapter1, formBuilder1, router1);
  }

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
      this.router1.navigate([`takings/view/${salesRow[0]}`]);
    }
  }
}
