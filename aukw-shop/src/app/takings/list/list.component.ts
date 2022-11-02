import { Component, OnInit } from '@angular/core';
import { AuthenticationService, TakingsService } from '@app/_services';
import { TakingsSummary, User } from '@app/_models';
import { concatMap } from 'rxjs/operators';

@Component({ templateUrl: 'list.component.html' })
export class TakingsListComponent implements OnInit {
  takingslist!: TakingsSummary[];
  takingslistNotInQB!: TakingsSummary[];
  user!: User;

  constructor(
    private takingsService: TakingsService,
    private authenticationService: AuthenticationService
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {
    this.takingsService.getSummary(1).subscribe((takingslist) => {
      this.takingslist = takingslist;
      this.takingslistNotInQB = this.takingslist.filter((x) => x.quickbooks == false);      
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

  //   this.takingslistNotInQB
  //   .concatMap(
  //     switchMap((u: User) => { 
  //         this.form.patchValue(u);
  //         return this.qbConnDetsService.getById(u.id);
  //     })
  // )
    
  }
}
