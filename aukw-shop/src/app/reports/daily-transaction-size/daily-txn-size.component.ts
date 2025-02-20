import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';

import { tap } from 'rxjs/operators';

import { environment } from '@environments/environment';

import { AvgDailyTransactionData } from '@app/_models';
import { ReportService } from '@app/_services';
import { AbstractChartReportComponent } from '../chart-report.component';

@Component({
  templateUrl: './daily-txn-size.component.html',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterLink],
})
export class DailyTransactionSizeComponent extends AbstractChartReportComponent<
  AvgDailyTransactionData[]
> {
  private reportService = inject(ReportService);

  override refreshSummary() {
    this.reportService
      .getAvgDailyTransactionData(environment.HARROWROAD_SHOPID)
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
}
