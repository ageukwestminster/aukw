import { Component, inject } from '@angular/core';
import { CommonModule, NgFor, NgIf } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { concatMap } from 'rxjs/operators';
import { NgbAccordionModule } from '@ng-bootstrap/ng-bootstrap';
import { environment } from '@environments/environment';

import {
  AvgWeeklySalesData,
  AvgWeeklySalesDataByQuarter,
  DateRange,
  DateRangeEnum,
} from '@app/_models';
import { ReportService } from '@app/_services';
import { AbstractChartReportComponent } from '../chart-report.component';
import { DateRangeChooserComponent } from '@app/shared';

@Component({
  templateUrl: './weekly-sales.component.html',
  standalone: true,
  imports: [
    CommonModule,
    DateRangeChooserComponent,
    NgbAccordionModule,
    NgFor,
    NgIf,
    ReactiveFormsModule,
    RouterLink,
  ],
})
export class WeeklySalesComponent extends AbstractChartReportComponent<AvgWeeklySalesData> {
  chartData: AvgWeeklySalesDataByQuarter[] = [];

  private reportService = inject(ReportService);

  readonly INITIALDATERANGE: DateRangeEnum = DateRangeEnum.LAST_QUARTER;

  override ngOnInit() {
    let dtRng = this.dateRangeAdapter.enumToDateRange(this.INITIALDATERANGE);

    this.form = this.formBuilder.group({
      dateRange: [this.INITIALDATERANGE],
      startDate: [dtRng.startDate],
      endDate: [dtRng.endDate],
    });

    this.onDateRangeChanged(this.INITIALDATERANGE);
  }

  override refreshSummary(startDate: string, endDate: string) {
    this.loading = true;
    this.reportService
      .getAverageWeeklySalesData(
        startDate,
        endDate,
        environment.HARROWROAD_SHOPID,
      )
      .pipe(
        concatMap((response) => {
          this.data = response;
          return this.reportService.getAverageWeeklySalesByQuarter(
            environment.HARROWROAD_SHOPID,
          );
        }),
      )
      .subscribe({
        next: (response) => (this.chartData = response),
        error: (error: any) => {
          this.loading = false;
          this.data = new AvgWeeklySalesData();
          this.chartData = [];
          this.alertService.error(error, { autoClose: false });
        },
        complete: () => (this.loading = false),
      });
  }
}
