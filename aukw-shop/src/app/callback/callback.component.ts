import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { environment } from '@environments/environment';
import { AlertService, AuthenticationService } from '@app/_services';

@Component({
  template: '',
})
export class CallbackComponent implements OnInit {
  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private alertService: AlertService,
    private authenticationService: AuthenticationService,
  ) {}

  public ngOnInit(): void {
    console.log('Callback route: ' + this.router.url);
    const code = this.route.snapshot.queryParamMap.get('code');
    var realmId = this.route.snapshot.queryParamMap.get('realmId');
    const state = this.route.snapshot.queryParamMap.get('state');

    if (!realmId) {
      realmId = this.route.snapshot.queryParamMap.get('realmid');
    }

    if (!code || !state || !realmId) {
      this.alertService.error(
        'Error: Invalid parameters passed to callback. To use ' +
          'this endpoint you must supply values for: ' +
          "'code', 'realmId' and 'state'.",
        { autoClose: false, keepAfterRouteChange: true },
      );
      window.location.href = environment.loginUrl;
    } else if (
      realmId != environment.qboCharityRealmID &&
      realmId != environment.qboEnterprisesRealmID
    ) {
      this.alertService.error('Error: Invalid realmid', {
        autoClose: false,
        keepAfterRouteChange: true,
      });
      window.location.href = environment.loginUrl;
    }

    // use the auth service and the supplied token to log in
    this.authenticationService.callback(code!, realmId!, state!).subscribe({
      next: () => {
        console.log('Logged in.');
        if (this.authenticationService.userValue) {
          this.router.navigate(['/']);
        } else {
          console.log('Unknown error.');
        }
      },
      error: (error) => {
        this.alertService.error('Quickbooks Callback failed: ' + error, {
          autoClose: false,
        });
      },
    });
  }
}
