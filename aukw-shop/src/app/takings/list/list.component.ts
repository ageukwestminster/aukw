import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { environment } from '@environments/environment';

import {
  AlertService,
  AuditLogService,
  AuthenticationService,
  LoadingIndicatorService,
  TakingsService,
} from '@app/_services';
import { ApiMessage, TakingsFilter, TakingsSummary, User } from '@app/_models';
import { TakingsRowComponent } from './row.component';
import { TakingsFilterComponent } from '../filter/takings-filter.component';

import {
  from,
  of,
  merge,
  map,
  shareReplay,
  switchMap,
  reduce,
  tap,
  mergeMap,
  toArray,
  Observable,
} from 'rxjs';

@Component({
  templateUrl: 'list.component.html',
  styleUrl: './list.component.css',
  standalone: true,
  imports: [
    CommonModule,
    RouterLink,
    TakingsRowComponent,
    TakingsFilterComponent,
  ],
})
export class TakingsListComponent implements OnInit {
  takingslist!: TakingsSummary[];
  average: number = 0;
  user!: User;
  loading: boolean = false;
  filter!: TakingsFilter;
  total: TakingsSummary | null = null;

  private loadingIndicatorService = inject(LoadingIndicatorService);
  private takingsService = inject(TakingsService);
  private authenticationService = inject(AuthenticationService);
  private alertService = inject(AlertService);
  private auditLogService = inject(AuditLogService);

  constructor() {
    this.user = this.authenticationService.userValue;
  }

  get isProduction() {
    return environment.production;
  }

  get showAddToQuickbooksButton() {
    return (
      this.user.isAdmin &&
      this.takingslist &&
      this.takingslist.filter((t) => !t.quickbooks).length > 0
    );
  }

  ngOnInit() {
    this.refreshList();
  }

  refreshList() {
    this.updateTotals(
      this.takingsService.getSummary(environment.HARROWROAD_SHOPID, ''),
    );
  }

  /* remove takings from visible list */
  takingsWasDeleted(takings: TakingsSummary): void {
    this.takingslist = this.takingslist.filter((x) => x.id !== takings.id);
  }

  takingsWasAddedToQB(takings: TakingsSummary): void {
    let updateItem = this.takingslist.find((x) => x.id == takings.id);

    if (updateItem) {
      let index = this.takingslist.indexOf(updateItem);
      //Replace the stored takings item with the supplied takings item
      this.takingslist[index] = takings;
    }
  }

  addAllToQuickbooks() {
    const takingslistNotInQB = this.takingslist.filter((t) => !t.quickbooks);

    from(takingslistNotInQB)
      .pipe(
        mergeMap((t) => this.takingsService.addToQuickbooks(t.id)),
        toArray(),
        tap((list: ApiMessage[]) => {
          list.forEach((msg) => {
            this.auditLogService.log(
              this.user,
              'INSERT',
              msg.message,
              'SalesReceipt',
              msg.id,
            );
          });
        }),
        this.loadingIndicatorService.createObserving({
          loading: () =>
            `Adding daily sales receipts to Enterprises QuickBooks`,
          success: (result) =>
            `Successfully added ${result.length} sales receipts to QuickBooks.`,
          error: (err) => `${err}`,
        }),
        shareReplay(1),
      )
      .subscribe({
        error: (e) => {
          this.alertService.error(e, { autoClose: false });
        },
        complete: () => this.refreshList(),
      });
  }

  takingsUpdated(takings: TakingsSummary[]) {
    this.updateTotals(of(takings));
  }

  takingsFilterUpdated(filter: TakingsFilter) {
    this.filter = filter;
  }

  filterIsLoading(value: boolean) {
    this.loading = value;
  }

  updateTotals(summary$: Observable<TakingsSummary[]>) {
    summary$
      .pipe(
        tap((response) => (this.takingslist = response)),
        // switchMap converts Observable<TakingSummary[]> (complex object)
        // to Observable<number> (daily sales)
        switchMap((dataArray: TakingsSummary[]) => {
          const obs = dataArray.map((x) => {
            return of(x);
          });
          return merge(...obs);
        }),
        // reduce calculates total sum & count
        reduce(
          (prev: { sum: TakingsSummary; count: number }, current) => {
            return { sum: prev.sum.add(current), count: prev.count + 1 };
          },
          { sum: new TakingsSummary(), count: 0 },
        ),
        tap((x) => (this.total = x.sum)),
        // map calculates average
        map((x) => {
          return x.sum.daily_net_sales / x.count;
        }),
      )
      .subscribe((average) => (this.average = average));
  }
}
