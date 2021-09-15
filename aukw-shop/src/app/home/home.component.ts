import { Component, OnInit } from '@angular/core';

import { User } from '@app/_models';
import {
  AuthenticationService,
} from '@app/_services';

@Component({ templateUrl: 'home.component.html' })
export class HomeComponent implements OnInit {
  loading = false;
  user: User;

  constructor(
    private authenticationService: AuthenticationService,
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {  }
}