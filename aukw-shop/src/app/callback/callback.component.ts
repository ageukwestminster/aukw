import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { environment } from '@environments/environment';
import { AlertService, AuthenticationService} from '@app/_services';

@Component({
  templateUrl: './callback.component.html'
})
export class CallbackComponent implements OnInit {

  constructor(private route: ActivatedRoute, 
    private router: Router,
    private alertService: AlertService,
    private authenticationService: AuthenticationService,
    ) { }

  public ngOnInit():void {
    
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
    if (realmId != environment.quickbooksRealmID) {
      console.error("Error: 'realmId' does not match expected value.");
        window.location.href = environment.loginUrl;
    }
    


    // Handle token
    // ...
    window.location.href = environment.loginUrl;
    //this.router.navigate(['/']);
}

}
