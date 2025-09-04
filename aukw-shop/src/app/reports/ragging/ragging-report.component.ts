import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { reduce, tap } from 'rxjs';
import { concatMap, map } from 'rxjs/operators';
import { NgbAccordionModule } from '@ng-bootstrap/ng-bootstrap';
import { QBReportService } from '@app/_services';
import { AbstractChartReportComponent } from '../chart-report.component';
import { fromArrayToElement } from '@app/_helpers';
import {
  DateRangeEnum,
  RaggingChartData,
  RaggingQuarter,
  SalesByItem,
} from '@app/_models';
import { DateRangeChooserComponent, RaggingChartComponent } from '@app/shared';

@Component({
  standalone: true,
  imports: [
    CommonModule,
    DateRangeChooserComponent,
    NgbAccordionModule,
    RaggingChartComponent,
    RouterLink,
  ],
  templateUrl: './ragging-report.component.html',
  styleUrl: './ragging-report.component.css',
})
export class RaggingReportComponent
  extends AbstractChartReportComponent<SalesByItem[]>
  implements OnInit
{
  tableData: RaggingQuarter[] = [];
  tableTotal!: RaggingQuarter;
  raggingChartData!: RaggingChartData;

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
    // Set flag to tell user to expect a wait
    this.loading = true;
    // initialise chart data arrays
    this.raggingChartData = new RaggingChartData();

    this.reportService
      .getSalesByItem(startDate, endDate)
      .pipe(
        tap((response) => (this.data = response)),
        // This converts Observable<SalesByItem[]> to Observable<SalesByItem>
        fromArrayToElement(),

        // reduce calculates total sum
        reduce((prev: SalesByItem, current) => {
          // Only include ragging items in calculation of sum
          return current.israg ? prev.add(current) : prev;
        }, new SalesByItem()),

        //assign to class variable and query for ragging by quarter
        concatMap((raggingTotals: SalesByItem) => {
          this.total = raggingTotals;
          return this.reportService.raggingByQuarter();
        }),

        // assign to class variable
        tap((response) => (this.tableData = response)),

        // Convert to Obs of RaggingQuarter rather than RaggingQuarter[]
        fromArrayToElement(),

        // Map the historical data into chart data form
        map((raggingQuarter) => {
          //Don't know how to make async
          this.adaptQBODataToChartFormat(raggingQuarter);
          return raggingQuarter;
        }),

        // This reduce calculates total of ragging by item
        reduce((prev: RaggingQuarter, current) => {
          return prev.add(current);
        }, new RaggingQuarter()),
      )

      .subscribe({
        next: (raggingTotal: RaggingQuarter) =>
          (this.tableTotal = raggingTotal),
        error: (error: any) => {
          this.loading = false;
          this.data = [];
          this.total = new SalesByItem();
          this.tableTotal = new RaggingQuarter();
          this.tableData = [];
          this.alertService.error(error, { autoClose: false });
        },
        complete: () => (this.loading = false),
      });
  }

  adaptQBODataToChartFormat(ragging: RaggingQuarter): void {
    let timestamp = new Date(ragging.end).getTime();

    let data = this.raggingChartData;

    data.books.push([timestamp, ragging.books.amount]);
    data.clothing.push([timestamp, ragging.clothing.amount]);
    data.household.push([timestamp, ragging.household.amount]);
    data.shoes.push([timestamp, ragging.shoes.amount]);
    data.other.push([timestamp, ragging.other.amount + ragging.rummage.amount]);
    data.total.push([
      timestamp,
      this.Math.round(
        100 *
          (ragging.books.amount +
            ragging.clothing.amount +
            ragging.household.amount +
            ragging.shoes.amount +
            ragging.other.amount +
            ragging.rummage.amount),
      ) / 100,
    ]);
  }
}
