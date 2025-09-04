import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { NgbTooltip } from '@ng-bootstrap/ng-bootstrap';
import { QBAccountListEntry } from '@app/_models';
import { map, switchMap, tap } from 'rxjs/operators';
import { of } from 'rxjs';

import { AbstractChartReportComponent } from '../chart-report.component';
import { fromArrayToElement } from '@app/_helpers';
import { QBReportService } from '@app/_services';
import { DateRangeEnum } from '@app/_models';
import { DateRangeChooserComponent, IntercoTradeComponent } from '@app/shared';

@Component({
  templateUrl: './aukw-interco.component.html',
  styleUrl: './aukw-interco.component.css',
  standalone: true,
  imports: [
    CommonModule,
    IntercoTradeComponent,
    NgbTooltip,
    RouterLink,
    ReactiveFormsModule,
    DateRangeChooserComponent,
  ],
})
export class AukwIntercoComponent
  extends AbstractChartReportComponent<QBAccountListEntry[]>
  implements OnInit
{
  selectedTrade: QBAccountListEntry | null = null;
  otherCompanyTrades: QBAccountListEntry[] = [];

  private reportService = inject(QBReportService);

  readonly INITIALDATERANGE: DateRangeEnum = DateRangeEnum.LAST_SIX_MONTHS;

  /**
   * Override the default constructor on the base class because
   * I want to use a non-default date range, namely 'Last 6 Months'.
   */
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
      .getIntercoAccountLedger(startDate, endDate, this.enterprises)
      .pipe(
        tap(() => (this.data = [])),

        // Convert from Observable<T[]> to Observable<T>
        fromArrayToElement(),

        map((accountListEntry) => {
          return new QBAccountListEntry(accountListEntry);
        }),

        switchMap((accountListEntry) => {
          this.data.push(accountListEntry);
          return this.reportService.getIntercoAccountLedger(
            startDate,
            endDate,
            !this.enterprises,
          );
        }),

        switchMap((response) => {
          this.otherCompanyTrades = response;
          this.data.forEach((item) => {
            var findEntries = this.otherCompanyTrades.filter(
              (x) => x.date == item.date && x.amount == item.amount,
            );
            if (findEntries && findEntries.length) {
              item.matching_txn = findEntries[0].type;
            }
          });

          return of(true);
        }),
      )
      .subscribe({
        error: (error: any) => {
          this.loading = false;
          this.data = [];
          this.alertService.error(error, { autoClose: false });
        },
        complete: () => (this.loading = false),
      });
  }

  formatPositiveNumber(amount: number | string): string {
    if (typeof amount === 'number' && amount > 0) {
      return String(amount);
    } else {
      return '';
    }
  }
  formatNegativeNumber(amount: number | string): string {
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

  onRowClick(item: QBAccountListEntry) {
    this.selectedTrade = item;
  }
}
