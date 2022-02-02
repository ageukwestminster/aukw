import { Component, OnInit } from '@angular/core';
import { Location } from '@angular/common';
import { Router, ActivatedRoute } from '@angular/router';
import { FormArray, FormBuilder, FormGroup, Validators } from '@angular/forms';

import { Observable } from 'rxjs';

import { TakingsService, AlertService, AuthenticationService, ShopService } from '@app/_services';
import { MustMatch } from '@app/_helpers';
import { Shop, User, Takings, FormMode } from '@app/_models';

@Component({ templateUrl: 'add-edit.component.html' })
export class TakingsAddEditComponent implements OnInit {
    form!: FormGroup;
    id!: number;
    shops$!: Observable<Shop[]>;
    formMode!: FormMode;
    loading = false;
    submitted = false;
    user! : User;    

    constructor(
        private formBuilder: FormBuilder,
        private route: ActivatedRoute,
        private router: Router,
        private takingsService: TakingsService,
        private alertService: AlertService,
        private authenticationService: AuthenticationService,
        private shopService: ShopService,
        private location: Location
    ) {
        this.user = this.authenticationService.userValue;
        this.shops$ = this.shopService.getAll();
    }

    ngOnInit() {
        this.loading = true;

        this.id = this.route.snapshot.params['id'];

        if (!this.id) {
            this.formMode = FormMode.Add;
        } else {
            this.formMode = FormMode.Edit;
        }

        this.form = this.formBuilder.group({
            date: ['', Validators.required],
            shopid: [null, Validators.required],
            departmentSales: new FormArray([]),
            customers_num_total: ['', Validators.required],
            cash_to_bank: ['', Validators.required],
            credit_cards: [''],
            operating_expenses: [''],
            volunteer_expenses: [''],
            cash_difference: [''],
            comments: [''],
        });

        if (this.formMode != FormMode.Add) {
            this.takingsService.getById(this.id)
                .subscribe(x => this.form.patchValue(x))
                .add(() => this.loading = false);
        }
    }

    // convenience getters for easy access to form fields
    get f() { return this.form.controls; }
    get d() { return this.f.departmentSales as FormArray; }
    get departmentSalesFormGroups() {
        return this.d.controls as FormGroup[];
      }

    onAddDepartmentSales(number_of_sales = '', sales = '') {
        this.d.push(
            this.formBuilder.group({
                number_of_sales: [number_of_sales, [Validators.required]],
                sales: [sales, [Validators.required]],
            })
        );
    }

    onRemoveDepartmentSales(index: number) {
    if (this.d.length > 1 && index) {
        this.d.removeAt(index);
    }
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
        if (this.formMode == FormMode.Add) {
            this.createTakings();
        } else {
            this.updateTakings();
        }
    }

    goBack()
    {
        // use of location object taken from https://stackoverflow.com/a/41953992/6941165
        this.location.back(); // <-- go back to previous location on cancel
    }

    get isAdd() { return this.formMode == FormMode.Add; }
    get isEdit() { return this.formMode == FormMode.Edit; }
    
    private createTakings() {
        this.takingsService.create(this.form.value)
            .subscribe(() => {
                this.alertService.success('Takings added', { keepAfterRouteChange: true });
                this.router.navigate(['../'], { relativeTo: this.route });
            })
            .add(() => this.loading = false);
    }

    private updateTakings() {
        this.takingsService.update(this.id, this.form.value)
            .subscribe(() => {
                this.alertService.success('Takings updated', { keepAfterRouteChange: true });

                this.location.back();                
            })
            .add(() => this.loading = false);
    }

}