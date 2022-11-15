import { Component, OnInit } from '@angular/core';
import { Location } from '@angular/common';
import { NgbPanelChangeEvent } from '@ng-bootstrap/ng-bootstrap';
import { AuthenticationService } from '@app/_services';
import { User } from '@app/_models';

@Component({
  selector: 'app-reports',
  templateUrl: './reports.component.html',
})
export class ReportsComponent implements OnInit {
  user: User;
  panelOpen: boolean[] = [false, false, false, false]; // 4 Accordians

  constructor(
    private location: Location,
    private authenticationService: AuthenticationService
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit(): void {}

  goBack() {
    this.location.back();
    return false; // don't propagate event
  }

  beforeChange(event: NgbPanelChangeEvent) {
    // panelID is one of 'static-1', 'static-2','static-3','static-4'
    let panelId =
      parseInt(event.panelId.substring(event.panelId.length - 1)) - 1;

    this.panelOpen[panelId] = event.nextState;
  }
}
