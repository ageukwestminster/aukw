import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
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
    console.log("Hello from callback");
    const code = this.route.snapshot.queryParamMap.get('code');
    const realmId = this.route.snapshot.queryParamMap.get('realmId');
    const state = this.route.snapshot.queryParamMap.get('state');

    if (!code || !state || !realmId) {
      this.alertService.error("Error: Empty callback parameters from Intuit.")
      this.router.navigate(['/']);
    }

    // Handle token
    // ...
    window.location.href = "http://localhost:4200/";
    //this.router.navigate(['/']);
}

}
