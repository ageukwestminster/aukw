import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

import { AuthenticationService, QBConnectionService } from '@app/_services';

import { QBAuthUri } from '@app/_models';

@Component({
  templateUrl: 'login.component.html',
  styles: ['img { max-width:340px; max-height:240px;}'],
})
export class LoginComponent implements OnInit {
  loginForm!: FormGroup;
  loading = false;
  submitted = false;
  returnUrl!: string;
  error = '';
  windowHandle!: Window | null;

  constructor(
    private formBuilder: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private authenticationService: AuthenticationService,
    private qbConnService: QBConnectionService,
  ) {
    // redirect to home if already logged in
    if (this.authenticationService.userValue) {
      this.router.navigate(['/']);
    }
  }

  ngOnInit() {
    this.loginForm = this.formBuilder.group({
      username: ['', Validators.required],
      password: ['', Validators.required],
    });

    // get return url from route parameters or default to '/'
    this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/';
  }

  // convenience getter for easy access to form fields
  get f() {
    return this.loginForm.controls;
  }

  onSubmit() {
    this.submitted = true;

    // stop here if form is invalid
    if (this.loginForm.invalid) {
      return;
    }

    this.loading = true;
    this.authenticationService
      .login(this.f.username.value, this.f.password.value)
      .subscribe({
        next: () => {
          this.router.navigate([this.returnUrl]);
        },
        error: (error) => {
          this.error = error;
          this.loading = false;
        },
      });
  }

  /**
   * Change to the 'hover' image, when hovering over the intuit button
   * @param element The img element for the Intuit button
   */
  hoverOverIntuitButton(element: HTMLImageElement) {
    element.setAttribute('src', 'assets/images/Sign_in_blue_btn_med_hover.svg');
  }

  /**
   * Change back to the default image when hovering ends
   * @param element The img element for the Intuit button
   */
  unhoverOverIntuitButton(element: HTMLImageElement) {
    element.setAttribute(
      'src',
      'assets/images/Sign_in_blue_btn_med_default.svg',
    );
  }

  /**
   * Redirect the user to the QBO OAuth2 login screen. This begins the OAuth2 process.
   * If the QBO login is successful the user will be directed back to the
   * callback component.
   * @param event Click on the button
   * @returns false
   */
  redirectToIntuitSSO(event: Event) {
    event.stopPropagation();
    //event.preventDefault();
    this.qbConnService.getAuthUri().subscribe((uri: QBAuthUri) => {
      if (uri && uri.authUri) {
        // Open the QB Auth uri in a new tab or window
        window.location.href = uri.authUri;
      }
    });
    return false;
  }
}
