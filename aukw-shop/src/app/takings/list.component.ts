import { Component, OnInit } from '@angular/core';
import { AuthenticationService, TakingsService } from '@app/_services';
import { Takings, User } from '@app/_models';

@Component({ templateUrl: 'list.component.html' })
export class TakingsListComponent implements OnInit {
  takingslist!: Takings[];
  user!: User;

  constructor(
    private takingsService: TakingsService,
    private authenticationService: AuthenticationService
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {
    this.takingsService.getByShopID(1).subscribe((takingslist) => {
      this.takingslist = takingslist;
    });
  }

  takingsWasDeleted(takings: Takings): void {
    this.takingslist = this.takingslist.filter((x) => x.id !== takings.id);
  }
}
