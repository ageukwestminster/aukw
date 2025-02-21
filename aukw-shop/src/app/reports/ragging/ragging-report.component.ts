import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, NgIf } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import {
  NgbAccordionModule,
  NgbDatepickerModule,
} from '@ng-bootstrap/ng-bootstrap';
import { of, merge, map, switchMap, reduce, tap } from 'rxjs';
import { environment } from '@environments/environment';
import { QBReportService } from '@app/_services';
import { AbstractChartReportComponent } from '../chart-report.component';
import { DateRange, DateRangeEnum, SalesByItem } from '@app/_models';
import { DateRangeChooserComponent } from '@app/shared';

@Component({
  standalone: true,
  imports: [
    CommonModule,
    DateRangeChooserComponent,
    NgbAccordionModule,
    NgbDatepickerModule,
    NgIf,
    ReactiveFormsModule,
    RouterLink,
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

    this.onDateRangeChanged(this.INITIALDATERANGE);
  }

  dateRangeChanged(dateRange: DateRange) {
    this.refreshSummary(dateRange.startDate, dateRange.endDate);
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
        // reduce calculates total sum & count
        reduce(
          (prev: { sum: SalesByItem; count: number }, current) => {
            return {
              sum: current.israg ? prev.sum.add(current) : prev.sum,
              count: prev.count + 1,
            };
          },
          { sum: new SalesByItem(), count: 0 },
        ),
      )
      .subscribe({
        next: (reduced) => (this.total = reduced.sum),
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
