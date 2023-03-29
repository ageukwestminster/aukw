import { Component, OnInit } from '@angular/core';
import {
  AlertService,
  AuthenticationService,
  TakingsService,
} from '@app/_services';
import { ApiMessage, TakingsFilter, TakingsSummary, User } from '@app/_models';
import { environment } from '@environments/environment';

import { from, Observable, of, merge, map } from 'rxjs';
import { concatMap, switchMap, reduce } from 'rxjs/operators';

@Component({ templateUrl: 'list.component.html' })
export class TakingsListComponent implements OnInit {
  takingslist!: TakingsSummary[];
  takingslistNotInQB!: TakingsSummary[];
  average$!: Observable<number>;
  user!: User;
  loading: boolean = false;
  filter!: TakingsFilter;

  constructor(
    private takingsService: TakingsService,
    private authenticationService: AuthenticationService,
    private alertService: AlertService
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {
    const takings$: Observable<TakingsSummary[]> =
      this.takingsService.getSummary(environment.HARROWROAD_SHOPID, '');

    this.average$ = takings$.pipe(
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
        { sum: 0, count: 0 }
      ),
      // map calculates average
      map((x) => x.sum / x.count)
    );

    takings$.subscribe((takingslist: TakingsSummary[]) => {
      this.takingslist = takingslist;
      this.takingslistNotInQB = this.takingslist.filter(
        (x) => x.quickbooks == false
      );
    });
  }

  /* remove takings from visible list */
  takingsWasDeleted(takings: TakingsSummary): void {
    this.takingslist = this.takingslist.filter((x) => x.id !== takings.id);
  }

  takingsWasAddedToQB(takings: TakingsSummary): void {
    let updateItem = this.takingslist.find((x) => x.id == takings.id);

    if (updateItem != null) {
      let index = this.takingslist.indexOf(updateItem);
      this.takingslist[index] = takings;
      this.takingslistNotInQB = this.takingslist.filter(
        (x) => x.quickbooks == false
      );
    }
  }

  addAllToQuickbooks() {
    if (!this.takingslistNotInQB || !this.takingslistNotInQB.length) return;

    from(this.takingslistNotInQB)
      .pipe(
        concatMap((t: TakingsSummary) => {
          t.isUpdating = true;

          return this.takingsService.addToQuickbooks(t.id).pipe(
            concatMap((msg: ApiMessage) => {
              t.quickbooks = true;
              t.isUpdating = false;
              this.alertService.success(msg.message, {
                keepAfterRouteChange: true,
              });
              return of(msg);
            })
          );
        })
      )
      .subscribe({
        error: (error) => {
          this.alertService.error(error.message, {
            autoClose: false,
          });
        },
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
