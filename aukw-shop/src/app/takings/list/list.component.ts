import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, NgFor, NgIf } from '@angular/common';
import { RouterLink } from '@angular/router';
import { environment } from '@environments/environment';

import {
  AlertService,
  AuthenticationService,
  LoadingIndicatorService,
  TakingsService,
} from '@app/_services';
import { TakingsFilter, TakingsSummary, User } from '@app/_models';
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
} from 'rxjs';

@Component({
  templateUrl: 'list.component.html',
  standalone: true,
  imports: [
    CommonModule,
    NgFor,
    NgIf,
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

  private loadingIndicatorService = inject(LoadingIndicatorService);
  private takingsService = inject(TakingsService);
  private authenticationService = inject(AuthenticationService);
  private alertService = inject(AlertService);

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
    this.takingsService
      .getSummary(environment.HARROWROAD_SHOPID, '')
      .pipe(
        tap((response) => (this.takingslist = response)),
        // switchMap converts Observable<TakingSummary[]> (complex object)
        // to Observable<number> (daily sales)
        switchMap((dataArray: TakingsSummary[]) => {
          const obs = dataArray.map((x) => {
            return of(x.daily_net_sales);
          });
          return merge(...obs);
        }),
        // reduce calculates total sum & count
        reduce(
          (prev: { sum: number; count: number }, current) => {
            return { sum: prev.sum + current, count: prev.count + 1 };
          },
          { sum: 0, count: 0 },
        ),
        // map calculates average
        map((x) => x.sum / x.count),
      )
      .subscribe((average) => (this.average = average));
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
        this.loadingIndicatorService.createObserving({
          loading: () =>
            `Adding daily sales receipts to Enterprises Quickbooks`,
          success: (result) =>
            `Successfully added ${result.length} sales receipts to Quickbooks.`,
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
    this.takingslist = takings;
  }

  takingsFilterUpdated(filter: TakingsFilter) {
    this.filter = filter;
  }

  filterIsLoading(value: boolean) {
    this.loading = value;
  }
}
