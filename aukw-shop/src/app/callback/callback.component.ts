import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { environment } from '@environments/environment';
import { AlertService, AuthenticationService} from '@app/_services';

@Component({
  template: ''
})
export class CallbackComponent implements OnInit {

  constructor(private route: ActivatedRoute, 
    private router: Router,
    private alertService: AlertService,
    private authenticationService: AuthenticationService,
    ) { }

  public ngOnInit():void {
    console.log('Callback route: ' +this.router.url);
    const code = this.route.snapshot.queryParamMap.get('code');
    const realmId = this.route.snapshot.queryParamMap.get('realmId');
    const state = this.route.snapshot.queryParamMap.get('state');

    if (!code || !state || !realmId) {
      console.error("Error: Invalid parameters passed to callback. To use "
          + "this endpoint you must supply values for: " 
          + "'code', 'realmId' and 'state'.");
      window.location.href = environment.loginUrl;
    }

    //Check that we have the correct company to proceed
    if (realmId != '9130350604308576') {
      console.error("Error: 'realmId' does not match expected value.");
        window.location.href = environment.loginUrl;
    }



    // use the auth service and the supplied token to log in
    this.authenticationService.callback(code!, realmId!, state!)
    .subscribe({
      next: () => {
        if (this.authenticationService.userValue) {
          this.router.navigate(['/']);
        }
        else {
          console.log('Unknown error.');
        }
      },
      error: (error) => {
        this.alertService.error('QB Callback failed: ' + error, {
          autoClose: false,
        });
      },
    });

}

}
