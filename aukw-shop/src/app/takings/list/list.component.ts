import { Component, OnInit } from '@angular/core';
import {
  AlertService,
  AuthenticationService,
  TakingsService,
} from '@app/_services';
import { ApiMessage, TakingsSummary, User } from '@app/_models';
import { from, of } from 'rxjs';
import { concatMap } from 'rxjs/operators';

@Component({ templateUrl: 'list.component.html' })
export class TakingsListComponent implements OnInit {
  takingslist!: TakingsSummary[];
  takingslistNotInQB!: TakingsSummary[];
  user!: User;

  constructor(
    private takingsService: TakingsService,
    private authenticationService: AuthenticationService,
    private alertService: AlertService
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {
    this.takingsService.getSummary(1).subscribe((takingslist) => {
      this.takingslist = takingslist;
      this.takingslistNotInQB = this.takingslist.filter(
        (x) => x.quickbooks == false
      );
    });
  }

  takingsWasDeleted(takings: TakingsSummary): void {
    this.takingslist = this.takingslist.filter((x) => x.id !== takings.id);
  }

  takingsWasAddedToQB(takings: TakingsSummary): void {
    let updateItem = this.takingslist.find((x) => x.id == takings.id);

    if (updateItem != null) {
      let index = this.takingslist.indexOf(updateItem);
      this.takingslist[index] = takings;
    }
  }


  addAllToQuickbooks() {
    if (!this.takingslistNotInQB || !this.takingslistNotInQB.length) return;

    from(this.takingslistNotInQB)
      .pipe(
        concatMap((t: TakingsSummary) => {
          t.isUpdating = true;

          return this.takingsService
          .addToQuickbooks(t.id).pipe(
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
      .subscribe();
  }
}
