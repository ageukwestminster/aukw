import { Component, OnInit } from '@angular/core';
import {
  CommonModule,
  DatePipe,
  Location,
  NgClass,
  NgIf,
  NgStyle,
} from '@angular/common';
import { Router, ActivatedRoute } from '@angular/router';
import {
  AbstractControlOptions,
  FormBuilder,
  FormGroup,
  FormsModule,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';
import {
  NgbDateAdapter,
  NgbDateParserFormatter,
  NgbDateStruct,
  NgbDatepickerModule,
} from '@ng-bootstrap/ng-bootstrap';

import { Subscription } from 'rxjs';

import { environment } from '@environments/environment';

import {
  TakingsService,
  AlertService,
  AuthenticationService,
  ShopService,
  AuditLogService,
} from '@app/_services';

import { Shop, User, Takings, FormMode, ApiMessage } from '@app/_models';

import { CustomDateParserFormatter, NgbUTCStringAdapter, MustProvideNumberOfItems } from '@app/_helpers';

@Component({
  templateUrl: 'add-edit.component.html',
  providers: [
    { provide: NgbDateAdapter, useClass: NgbUTCStringAdapter },
    { provide: NgbDateParserFormatter, useClass: CustomDateParserFormatter },
    DatePipe,
  ],
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    NgbDatepickerModule,
    NgClass,
    NgIf,
    NgStyle,
    ReactiveFormsModule,
  ],
})
export class TakingsAddEditComponent implements OnInit {
  form!: FormGroup;
  id!: number;
  shops!: Shop[];
  formMode!: FormMode;
  loading = false;
  submitted = false;
  user!: User;
  cashDifference = 0;
  minimumNextDate: NgbDateStruct = { year: 2000, month: 1, day: 1 };
  prevMinimumNextDate: NgbDateStruct = { year: 2000, month: 1, day: 1 };

  sumOfNumber = 0;
  sumOfAmount = ''; // a string to allow easier rounding

  private subscription = new Subscription();

  constructor(
    private formBuilder: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private takingsService: TakingsService,
    private alertService: AlertService,
    private authenticationService: AuthenticationService,
    private shopService: ShopService,
    private location: Location,
    private datePipe: DatePipe,
    private auditLogService: AuditLogService,
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {
    this.loading = true;

    this.id = this.route.snapshot.params['id'];

    if (!this.id) {
      this.formMode = FormMode.Add;
    } else if (
      this.route.snapshot.url[0] &&
      this.route.snapshot.url[0].path &&
      this.route.snapshot.url[0].path == 'edit'
    ) {
      this.formMode = FormMode.Edit;
    } else {
      this.formMode = FormMode.View;
    }


    const formOptions: AbstractControlOptions = {
          validators: [
            MustProvideNumberOfItems('clothing'),
            MustProvideNumberOfItems('brica'),
            MustProvideNumberOfItems('books'),
            MustProvideNumberOfItems('linens'),
            MustProvideNumberOfItems('donations'),
            MustProvideNumberOfItems('other')
          ],
      };

    this.form = this.formBuilder.group(
      {
        date: [null],
        shopid: [{ value: environment.HARROWROAD_SHOPID, disabled: true }],
        clothing_num: [''],
        brica_num: [''],
        books_num: [''],
        linens_num: [''],
        donations_num: [''],
        other_num: [''],
        rag_num: [''],
        clothing: [''],
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
        quickbooks: [{ value: 0, disabled: true }],
      },
      formOptions,
    );

    this.subscription.add(
      this.form.valueChanges.subscribe((value: Takings) => {
        this.onChange(value);
      }),
    );

    // Fill shop dropdown
    this.shopService.getAll().subscribe((x) => {
      this.shops = x;
    });

    if (this.formMode != FormMode.Add) {
      this.takingsService
        .getById(this.id)
        .subscribe((x) => this.form.patchValue(x))
        .add(() => (this.loading = false));
    } else {
      // For new Takings set today's date and the minimum allowed date
      // in the datePicker. We set a minimum date so that users can't add
      // easily add takings with duplicate dates
      this.takingsService
        .getMostRecent(environment.HARROWROAD_SHOPID) // Find most recent Takings (usually yesterday's)
        .subscribe((x) => {
          let today = new Date();
          if (x.date) {
            let lastTradeDate = new Date(x.date);

            lastTradeDate.setDate(lastTradeDate.getDate() + 1); // Add one day

            let adapter = new NgbUTCStringAdapter();
            this.minimumNextDate = adapter.fromModel(
              this.datePipe.transform(lastTradeDate, 'yyyy-MM-dd'),
            )!;

            this.form.controls['date'].setValue(
              this.datePipe.transform(
                today > lastTradeDate ? today : lastTradeDate,
                'yyyy-MM-dd',
              ),
            );
          } else {
            this.form.controls['date'].setValue(
              this.datePipe.transform(today, 'yyyy-MM-dd'),
            );
          }
        })
        .add(() => (this.loading = false));
    }
  }

  ngOnDestroy() {
    this.subscription.unsubscribe();
  }

  writeValue(value: null | Takings): void {
    if (value) {
      this.form.reset(value);
    }
  }

  // convenience getter for easy access to form fields
  get f() {
    return this.form.controls;
  }

  onChange(value: Takings) {
    this.sumOfNumber =
      Number(value.books_num) +
      Number(value.brica_num) +
      Number(value.clothing_num) +
      Number(value.donations_num) +
      Number(value.linens_num) +
      Number(value.other_num) +
      Number(value.rag_num);
    this.sumOfAmount = (
      Number(value.books) +
      Number(value.brica) +
      Number(value.clothing) +
      Number(value.donations) +
      Number(value.linens) +
      Number(value.other) +
      Number(value.rag)
    ).toFixed(2);
    this.cashDifference =
      Math.round(
        (Number(value.operating_expenses) +
          Number(value.volunteer_expenses) +
          Number(value.cash_to_bank) +
          Number(value.credit_cards) -
          Number(this.sumOfAmount)) *
          100,
      ) / 100;
  }

  onSubmit() {
    this.submitted = true;

    // reset alerts on submit
    this.alertService.clear();

    // stop here if form is invalid
    if (this.form.invalid) {
      return;
    }

    this.form.controls['cash_difference'].setValue(this.cashDifference);

    this.loading = true;
    if (this.formMode == FormMode.Add) {
      this.createTakings();
    } else if (this.formMode == FormMode.Edit) {
      this.updateTakings();
    }
  }

  // /(change)="onDatePickerCheckboxChange($event.target)
  onDatePickerCheckboxChange(target: any) {
    if (target && target.checked) {
      this.minimumNextDate = this.prevMinimumNextDate;
    } else {
      this.prevMinimumNextDate = this.minimumNextDate;
      this.minimumNextDate = { year: 2000, month: 1, day: 1 };
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
  get isReadOnly() {
    return this.formMode == FormMode.View;
  }

  private createTakings() {
    this.takingsService
      .create(this.form.getRawValue())
      .subscribe({
        next: (msg: ApiMessage) => {
          this.auditLogService.log(
            this.user,
            'INSERT',
            msg.message,
            'Takings',
            msg.id,
          );
          this.alertService.success('Takings added', {
            keepAfterRouteChange: true,
          });
          this.router.navigate(['../'], { relativeTo: this.route });
        },
        error: (error) => {
          this.alertService.error('Takings not added', {
            autoClose: false,
          });
        },
      })
      .add(() => (this.loading = false));
  }

  private updateTakings() {
    this.takingsService
      .update(this.id, this.form.getRawValue())
      .subscribe({
        next: (msg: ApiMessage) => {
          this.auditLogService.log(
            this.user,
            'UPDATE',
            msg.message,
            'Takings',
            msg.id,
          );
          this.alertService.success('Takings updated', {
            keepAfterRouteChange: true,
          });

          this.location.back();
        },
        error: (error) => {
          this.alertService.error('Takings not updated', {
            autoClose: false,
          });
        },
      })
      .add(() => (this.loading = false));
  }
}
