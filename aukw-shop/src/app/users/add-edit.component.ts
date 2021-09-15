import { Component, OnInit } from '@angular/core';
import { Location } from '@angular/common';
import { Router, ActivatedRoute } from '@angular/router';
import { AbstractControlOptions, FormBuilder, FormGroup, Validators } from '@angular/forms';

import { UserService, AlertService, AuthenticationService } from '@app/_services';
import { MustMatch } from '@app/_helpers';
import { User, UserFormMode } from '@app/_models';

@Component({ templateUrl: 'add-edit.component.html' })
export class UserAddEditComponent implements OnInit {
    form!: FormGroup;
    id!: number;
    //isAddMode!: boolean;
    formMode!: UserFormMode;
    loading = false;
    submitted = false;
    apiUser! : User;    

    constructor(
        private formBuilder: FormBuilder,
        private route: ActivatedRoute,
        private router: Router,
        private userService: UserService,
        private alertService: AlertService,
        private authenticationService: AuthenticationService,
        private location: Location
    ) {
        this.apiUser = this.authenticationService.userValue;
    }

    ngOnInit() {
        this.id = this.route.snapshot.params['id'];

        if (!this.id) {
            this.formMode = UserFormMode.Add;
        } else if (this.id == this.apiUser.id) {
            this.formMode = UserFormMode.Profile;
        } else {
            this.formMode = UserFormMode.Edit;
        }
        
        // password not required in edit mode
        const passwordValidators = [Validators.minLength(6)];
        if (this.formMode == UserFormMode.Add) {
            passwordValidators.push(Validators.required);
        }

        const formOptions: AbstractControlOptions = { validators: MustMatch('password', 'confirmPassword') };
        this.form = this.formBuilder.group({
            fullname: ['', Validators.required],
            suspended: [false],
            title: [''],
            username: ['', [Validators.required]],
            email: ['', [Validators.email]],
            role: ['', Validators.required],
            password: ['', [Validators.minLength(8), (this.formMode == UserFormMode.Add) ? Validators.required : Validators.nullValidator]],
            confirmPassword: ['', (this.formMode == UserFormMode.Add) ? Validators.required : Validators.nullValidator]
        }, formOptions);

        if (this.formMode != UserFormMode.Add) {
            this.userService.getById(this.id)
                .subscribe(x => this.form.patchValue(x))
                .add(() => this.loading = false);
        }
    }

    // convenience getter for easy access to form fields
    get f() { return this.form.controls; }

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

    get isUserAdd() { return this.formMode == UserFormMode.Add; }
    get isUserEdit() { return this.formMode == UserFormMode.Edit; }
    get isUserProfile() { return this.formMode == UserFormMode.Profile; }

    private createUser() {
        this.userService.create(this.form.value)
            .subscribe(() => {
                this.alertService.success('User added', { keepAfterRouteChange: true });
                this.router.navigate(['../'], { relativeTo: this.route });
            })
            .add(() => this.loading = false);
    }

    private updateUser() {
        this.userService.update(this.id, this.form.value)
            .subscribe(() => {
                this.alertService.success('User updated', { keepAfterRouteChange: true });

                this.location.back();                
            })
            .add(() => this.loading = false);
    }

    onCancel()
    {
        // use of location object taken from https://stackoverflow.com/a/41953992/6941165
        this.location.back(); // <-- go back to previous location on cancel
    }
}