import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { concatMap } from 'rxjs/operators';
import { NgbAccordionModule } from '@ng-bootstrap/ng-bootstrap';
import { environment } from '@environments/environment';

import {
  AvgDailyTransactionData,
  AvgDailyTransactionDataByQuarter,
  DateRange,
  DateRangeEnum,
} from '@app/_models';
import { ReportService } from '@app/_services';
import { AbstractChartReportComponent } from '../chart-report.component';
import { DateRangeChooserComponent } from '@app/shared';

@Component({
  templateUrl: './daily-txn-size.component.html',
  standalone: true,
  imports: [
    CommonModule,
    DateRangeChooserComponent,
    NgbAccordionModule,
    ReactiveFormsModule,
    RouterLink,
  ],
})
export class DailyTransactionSizeComponent extends AbstractChartReportComponent<AvgDailyTransactionData> {
  chartData: AvgDailyTransactionDataByQuarter[] = [];

  private reportService = inject(ReportService);

  readonly INITIALDATERANGE: DateRangeEnum = DateRangeEnum.LAST_QUARTER;

  override ngOnInit() {
    let dtRng = this.dateRangeAdapter.enumToDateRange(this.INITIALDATERANGE);

    this.form = this.formBuilder.group({
      dateRange: [this.INITIALDATERANGE],
      startDate: [dtRng.startDate],
      endDate: [dtRng.endDate],
    });

    this.onDateRangeEnumSelected(this.INITIALDATERANGE);
  }

  override refreshSummary(startDate: string, endDate: string) {
    this.loading = true;
    this.reportService
      .getAvgDailyTransactions(
        startDate,
        endDate,
        environment.HARROWROAD_SHOPID,
      )
      .pipe(
        concatMap((response) => {
          this.data = response;
          return this.reportService.getAvgDailyTransactionsByQuarter(
            environment.HARROWROAD_SHOPID,
          );
        }),
      )
      .subscribe({
        next: (response) => (this.chartData = response),
        error: (error: any) => {
          this.loading = false;
          this.data = new AvgDailyTransactionData();
          this.chartData = [];
          this.alertService.error(error, { autoClose: false });
        },
        complete: () => (this.loading = false),
      });
  }
}
