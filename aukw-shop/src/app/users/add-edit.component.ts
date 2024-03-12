﻿import { Component, OnInit } from '@angular/core';
import { Location } from '@angular/common';
import { Router, ActivatedRoute } from '@angular/router';
import {
  AbstractControlOptions,
  FormBuilder,
  FormGroup,
  Validators,
} from '@angular/forms';

import { Observable } from 'rxjs';
import { switchMap } from 'rxjs/operators';

import {
  UserService,
  AlertService,
  AuthenticationService,
  ShopService,
  QBConnectionService,
} from '@app/_services';
import { MustMatch } from '@app/_helpers';
import {
  QBAuthUri,
  QBConnectionDetails,
  Shop,
  User,
  UserFormMode,
} from '@app/_models';

@Component({ templateUrl: 'add-edit.component.html' })
export class UserAddEditComponent implements OnInit {
  form!: FormGroup;
  id!: number;
  shops$!: Observable<Shop[]>;  
  formMode!: UserFormMode;
  loading = false;
  submitted = false;
  user!: User;
  windowHandle: any = null;
  refreshTokenExpiry: Date | null = null;
  connections$!: Observable<QBConnectionDetails[]>;

  constructor(
    private formBuilder: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private userService: UserService,
    private alertService: AlertService,
    private authenticationService: AuthenticationService,
    private shopService: ShopService,
    private qbConnDetsService: QBConnectionService,
    private location: Location,
  ) {
    this.user = this.authenticationService.userValue;
    this.shops$ = this.shopService.getAll();
    this.connections$ = this.qbConnDetsService.getAll(this.user.id);
  }

  ngOnInit() {
    this.id = this.route.snapshot.params['id'];

    if (!this.id) {
      this.formMode = UserFormMode.Add;
    } else if (this.id == this.user.id) {
      this.formMode = UserFormMode.Profile;
    } else {
      this.formMode = UserFormMode.Edit;
    }

    // password not required in edit mode
    const passwordValidators = [Validators.minLength(6)];
    if (this.formMode == UserFormMode.Add) {
      passwordValidators.push(Validators.required);
    }

    const formOptions: AbstractControlOptions = {
      validators: MustMatch('password', 'confirmPassword'),
    };
    this.form = this.formBuilder.group(
      {
        firstname: ['', Validators.required],
        surname: ['', Validators.required],
        suspended: [false],
        email: [null, [Validators.email]],
        title: [null],
        shopid: ['', Validators.required],
        username: ['', [Validators.required]],
        role: ['', Validators.required],
        password: [
          '',
          [
            Validators.minLength(8),
            this.formMode == UserFormMode.Add
              ? Validators.required
              : Validators.nullValidator,
          ],
        ],
        confirmPassword: [
          '',
          this.formMode == UserFormMode.Add
            ? Validators.required
            : Validators.nullValidator,
        ],
      },
      formOptions,
    );

    if (this.formMode != UserFormMode.Add) {
      this.userService
        .getById(this.id)
        .pipe(
          switchMap((u: User) => {
            this.form.patchValue(u);
            return this.qbConnDetsService.getDetails(u.id);
          }),
        )
        .subscribe((conn: any | null) => {
          this.refreshQBConnectionDetails(conn);
          this.loading = false;
        });
    }
  }

  //
  private refreshQBConnectionDetails(conn: QBConnectionDetails) {
    if (conn && conn.refreshtokenexpiry) {
      const t: string[] = conn.refreshtokenexpiry.split(/[- :]/);
      const tokenExpiry = new Date(
        Date.UTC(+t[0], +t[1] - 1, +t[2], +t[3], +t[4], +t[5]),
      );
      const nowDateAndTime = new Date();
      if (tokenExpiry > nowDateAndTime) {
        this.refreshTokenExpiry = tokenExpiry;
      } else {
        this.refreshTokenExpiry = null;
      }
    }
  }

  // convenience getter for easy access to form fields
  get f() {
    return this.form.controls;
  }

  //
  get isProfileEdit() {
    return this.formMode && this.formMode === UserFormMode.Profile;
  }

  onSubmit() {
    this.submitted = true;

    // reset alerts on submit
    this.alertService.clear();

    // stop here if form is invalid
    if (this.form.invalid) {
      return;
    }

    this.loading = true;
    if (this.formMode == UserFormMode.Add) {
      this.createUser();
    } else {
      this.updateUser();
    }
  }

  goBack() {
    // use of location object taken from https://stackoverflow.com/a/41953992/6941165
    this.location.back(); // <-- go back to previous location on cancel
  }

  get isUserAdd() {
    return this.formMode == UserFormMode.Add;
  }
  get isUserEdit() {
    return this.formMode == UserFormMode.Edit;
  }
  get isUserProfile() {
    return this.formMode == UserFormMode.Profile;
  }

  private createUser() {
    this.userService
      .create(this.form.value)
      .subscribe(() => {
        this.alertService.success('User added', { keepAfterRouteChange: true });
        this.router.navigate(['../'], { relativeTo: this.route });
      })
      .add(() => (this.loading = false));
  }

  private updateUser() {
    this.userService
      .update(this.id, this.form.value)
      .subscribe(() => {
        this.alertService.success('User updated', {
          keepAfterRouteChange: true,
        });

        this.location.back();
      })
      .add(() => (this.loading = false));
  }

  // Connect the QB company file to this app
  makeQBConnection() {
    this.qbConnDetsService.getAuthUri().subscribe((uri: QBAuthUri) => {
      if (uri && uri.authUri) {
        // Open the QB Auth uri in a new tab or window
        this.windowHandle = window.open(uri.authUri);
      }
    });
    return false;
  }

  // Disconnect the QB Company file from this app
  revokeQBConnection() {
    this.qbConnDetsService
      .revokeQBConnection()
      .pipe(
        switchMap(() => {
          return this.qbConnDetsService.getDetails(this.user.id);
        }),
      )
      .subscribe((conn: QBConnectionDetails) => {
        this.refreshQBConnectionDetails(conn);
        this.alertService.success('QB Connection deleted');
      });
    return false;
  }
}
