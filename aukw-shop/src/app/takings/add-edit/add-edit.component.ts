import { Component, OnInit, SimpleChanges } from '@angular/core';
import { Location } from '@angular/common';
import { Router, ActivatedRoute } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

import { Subscription } from 'rxjs';

import {
  TakingsService,
  DepartmentService,
  AlertService,
  AuthenticationService,
  ShopService,
} from '@app/_services';
import { Shop, User, Takings, FormMode } from '@app/_models';
//import { Console } from 'console';

@Component({ templateUrl: 'add-edit.component.html' })
export class TakingsAddEditComponent implements OnInit {
  form!: FormGroup;
  id!: number;
  shops!: Shop[];
  depts!: string[];
  formMode!: FormMode;
  loading = false;
  submitted = false;
  user!: User;

  private subscription = new Subscription();
  onChange: any = (_: Takings) => {};
  onTouch: any = () => {};

  constructor(
    private formBuilder: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private takingsService: TakingsService,
    private alertService: AlertService,
    private authenticationService: AuthenticationService,
    private shopService: ShopService,
    private deptService: DepartmentService,
    private location: Location
  ) {
    this.user = this.authenticationService.userValue;
    this.depts = this.deptService.getAll();
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
      date: [null, Validators.required],
      shopid: [{ value: 1, disabled: true }],
      clothing_num: ['', Validators.required],
      brica_num: [''],
      books_num: [''],
      linens_num: [''],
      donations_num: [''],
      other_num: [''],
      rag_num: [''],
      clothing: ['', Validators.required],
      brica: [''],
      books: [''],
      linens: [''],
      donations: [''],
      other: [''],
      rag: [''],
      customers_num_total: ['', Validators.required],
      cash_to_bank: ['', Validators.required],
      credit_cards: [''],
      operating_expenses: [''],
      volunteer_expenses: [''],
      cash_difference: [''],
      comments: [''],
    });

    this.subscription.add(
      this.form.valueChanges.subscribe((value: Takings) => {
        this.onChange(value);
      })
    );

    // Fill shop dropdown
    this.shopService.getAll().subscribe((x) => {
      this.shops = x;
    });

    if (this.formMode === FormMode.Add) {
      // Initialize the 'Date' field with today's date for New Takings
      this.form.controls['date'].setValue(
        // From https://stackoverflow.com/a/35922073/6941165
        new Date().toISOString().slice(0, 10)
      );
    }

    if (this.formMode != FormMode.Add) {
      this.takingsService
        .getById(this.id)
        .subscribe((x) => this.form.patchValue(x))
        .add(() => (this.loading = false));
    } else {
      this.loading = false;
    }
  }

  ngOnDestroy() {
    this.subscription.unsubscribe();
  }

  ngOnChanges(simpleChanges: SimpleChanges) {
    if (simpleChanges['touched'] && simpleChanges['touched'].currentValue) {
      this.form.markAllAsTouched();
    }

    // only run when property "data" changed
    if (simpleChanges['transactions']) {
      //console.log(`OnChanges: Tx length: ${this.transactions?.length}`);
    }
  }

  writeValue(value: null | Takings): void {
    if (value) {
      this.form.reset(value);
    }
  }

  registerOnChange(fn: () => {}): void {
    this.onChange = fn;
  }

  registerOnTouched(fn: (_: Takings) => {}): void {
    this.onTouch = fn;
  }

  // convenience getters for easy access to form fields
  get f() {
    return this.form.controls;
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

  goBack() {
    // use of location object taken from https://stackoverflow.com/a/41953992/6941165
    this.location.back(); // <-- go back to previous location on cancel
  }

  get isAdd() {
    return this.formMode == FormMode.Add;
  }
  get isEdit() {
    return this.formMode == FormMode.Edit;
  }

  private createTakings() {
    this.takingsService
      .create(this.form.value)
      .subscribe(() => {
        this.alertService.success('Takings added', {
          keepAfterRouteChange: true,
        });
        this.router.navigate(['../'], { relativeTo: this.route });
      })
      .add(() => (this.loading = false));
  }

  private updateTakings() {
    this.takingsService
      .update(this.id, this.form.value)
      .subscribe(() => {
        this.alertService.success('Takings updated', {
          keepAfterRouteChange: true,
        });

        this.location.back();
      })
      .add(() => (this.loading = false));
  }
}
