import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, Location, NgClass } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { NgbTooltip, NgbModalOptions } from '@ng-bootstrap/ng-bootstrap';
import { QBAccountListEntry } from '@app/_models';
import { map, mergeMap, switchMap, tap } from 'rxjs/operators';
import { of } from 'rxjs';

import { AbstractChartReportComponent } from '../chart-report.component';
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

  private location = inject(Location);
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
        mergeMap((accountEntries: QBAccountListEntry[]) => accountEntries),

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

  /** Show the intercompany account from the other QuickBooks company. */
  switchCompany() {
    this.enterprises = !this.enterprises;
    this.refreshSummary(this.form.value.startDate, this.form.value.endDate);
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
    if (Number.parseFloat(item.amount.toString()) < 0) {
      this.alertService.info(
        'Only expense trades can be entered via this screen.',
      );
      return;
    }

    if (this.enterprises) return; // only allow trades to be entered from the Enterprises company

    /**
     * Set up the modal options
     * From {@link https://ng-bootstrap.github.io/#/components/modal/api}
     */
    const modalOptions = {
      backdrop: 'static',
      backdropClass: 'loading-indicator-backdrop',
      centered: true,
      fullscreen: 'md',
      size: 'md',
    } as NgbModalOptions;

    // Open the modal
    const modalRef = this.modalService.open(
      IntercoTradeComponent,
      modalOptions,
    );
    /** Communicate with the modal component. From {@link https://stackoverflow.com/a/48698760} */
    modalRef.componentInstance.existingTrade = item;
    modalRef.componentInstance.enterprises = this.enterprises;
  }

  /** Return to previous page */
  goBack() {
    this.location.back();
    return false; // don't propagate event
  }
}
