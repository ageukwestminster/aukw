import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, NgIf } from '@angular/common';

import { of, merge, switchMap, reduce, tap } from 'rxjs';
import { QBReportService } from '@app/_services';
import { AbstractChartReportComponent } from '../chart-report.component';
import { DateRangeEnum, SalesByItem } from '@app/_models';
import { DateRangeChooserComponent } from '@app/shared';

@Component({
  standalone: true,
  imports: [
    CommonModule,
    DateRangeChooserComponent,
    NgIf,
  ],
  templateUrl: './ragging-report.component.html',
  styleUrl: './ragging-report.component.css',
})
export class RaggingReportComponent
  extends AbstractChartReportComponent<SalesByItem[]>
  implements OnInit
{
  private reportService = inject(QBReportService);

  readonly INITIALDATERANGE: DateRangeEnum = DateRangeEnum.LAST_QUARTER;

  total!: SalesByItem;

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
      .getSalesByItem(startDate, endDate)
      .pipe(
        tap((response) => (this.data = response)),
        // switchMap converts Observable<SalesByItem[]> (complex object)
        // to Observable<SalesByItem>
        switchMap((dataArray: SalesByItem[]) => {
          const obs = dataArray.map((x) => {
            return of(x);
          });
          return merge(...obs);
        }),
        // reduce calculates total sum
        reduce((prev: SalesByItem, current) => {
          // Only total ragging items
          return current.israg ? prev.add(current) : prev;
        }, new SalesByItem()),
      )
      .subscribe({
        next: (raggingTotals: SalesByItem) => (this.total = raggingTotals),
        error: (error: any) => {
          this.loading = false;
          this.data = [];
          this.total = new SalesByItem();
          this.alertService.error(error, { autoClose: false });
        },
        complete: () => (this.loading = false),
      });
  }
}
