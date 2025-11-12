import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, NgClass } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { Location } from '@angular/common';
import {
  AbstractControlOptions,
  FormBuilder,
  FormGroup,
  Validators,
} from '@angular/forms';

import { Observable } from 'rxjs';

import {
  UserService,
  AlertService,
  AuthenticationService,
  ShopService,
  AuditLogService,
} from '@app/_services';
import { MustMatch } from '@app/_helpers';
import { ApiMessage, Shop, User, UserFormMode } from '@app/_models';

@Component({
  templateUrl: 'add-edit.component.html',
  styleUrls: ['./users.css'],
  standalone: true,
  imports: [CommonModule, NgClass, ReactiveFormsModule],
})
export class UserAddEditComponent implements OnInit {
  form!: FormGroup;
  id!: number;
  shops$!: Observable<Shop[]>;
  formMode!: UserFormMode;
  loading = false;
  submitted = false;
  user!: User;

  private formBuilder = inject(FormBuilder);
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private userService = inject(UserService);
  private alertService = inject(AlertService);
  private authenticationService = inject(AuthenticationService);
  private shopService = inject(ShopService);
  private location = inject(Location);
  private auditLogService = inject(AuditLogService);

  constructor() {
    this.user = this.authenticationService.userValue;
    this.shops$ = this.shopService.getAll();
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
        email: [null, { validators: [Validators.email], updateOn: 'blur' }],
        title: [null],
        shopid: [''],
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
      this.userService.getById(this.id).subscribe((u: User) => {
        this.form.patchValue(u);
        this.loading = false;
      });
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
      .subscribe((msg: ApiMessage) => {
        this.alertService.success('User added', { keepAfterRouteChange: true });
        this.router.navigate(['../'], { relativeTo: this.route });
        this.auditLogService.log(
          this.user,
          'INSERT',
          msg.message,
          'user',
          msg.id,
        );
      })
      .add(() => {
        this.loading = false;
      });
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
}
