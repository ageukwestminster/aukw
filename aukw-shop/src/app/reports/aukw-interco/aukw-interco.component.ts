import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, NgClass } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { NgbTooltip, NgbModalOptions } from '@ng-bootstrap/ng-bootstrap';
import { QBAccountListEntry } from '@app/_models';
import { map, switchMap, tap } from 'rxjs/operators';
import { from, concatMap, of, catchError } from 'rxjs';

import { AbstractChartReportComponent } from '../chart-report.component';
import { fromArrayToElement } from '@app/_helpers';
import { ModalService, QBReportService } from '@app/_services';
import { DateRangeEnum } from '@app/_models';
import { DateRangeChooserComponent, IntercoTradeComponent } from '@app/shared';

@Component({
  templateUrl: './aukw-interco.component.html',
  styleUrl: './aukw-interco.component.css',
  standalone: true,
  imports: [
    CommonModule,
    NgClass,
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
  /* 'true' if there is a matching trade in the other QBO company */
  matchExists: boolean[] = [];

  private reportService = inject(QBReportService);
  /** A wrapper for NgbModal to avoid aria-hidden warnings */
  public modalService = inject(ModalService);

  /* Default initial date range for the report */
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
        tap(() => (this.data = [])), // reset class-level data store

        // Convert from Observable<T[]> to Observable<T>
        fromArrayToElement(),

        map((accountListEntry) => {
          return new QBAccountListEntry(accountListEntry);
        }),

        // Now download the interco transactions from the other company
        switchMap((accountListEntry) => {
          this.data.push(accountListEntry);
          return this.reportService.getIntercoAccountLedger(
            startDate,
            endDate,
            !this.enterprises,
          );
        }),

        // Check if there are matching transactions in the other company
        switchMap((response) => {
          //this.otherCompanyTrades = response;
          this.matchExists = new Array<boolean>(this.data.length);
          let index = 0;
          this.data.forEach((item) => {
            var findEntries = response.filter(
              (x) => x.date == item.date && x.amount == item.amount,
            );
            if (findEntries && findEntries.length) {
              this.matchExists[index] = true;
            }
            index++;
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

  /* when the user clicks on a row in the table a add trade modal appears*/
  onRowClick(item: QBAccountListEntry) {
    if (Number.parseFloat(item.amount.toString()) < 0) return; // only allow expense trades to be entered
    const modalOptions = {
      backdrop: 'static',
      backdropClass: 'loading-indicator-backdrop',
      centered: true,
      fullscreen: 'md',
      size: 'lg',
    } as NgbModalOptions;
    const modalRef = this.modalService.open(
      IntercoTradeComponent,
      modalOptions,
    );
    modalRef.componentInstance.existingTrade = item;
    modalRef.componentInstance.enterprises = this.enterprises;

    return from(modalRef.result).pipe(
      catchError((err) => {
        return of();
      }),
    );
  }
}
