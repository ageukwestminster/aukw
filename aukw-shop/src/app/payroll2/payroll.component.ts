import { Component, inject, OnInit } from '@angular/core';
import { AsyncPipe, JsonPipe } from '@angular/common';
import {
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';
import { Observable, of } from 'rxjs';
import { environment } from '@environments/environment';
import {
  GrossToNetService,
  PayRunService,
  TaxYearService,
} from '@app/_services/payroll';
import { ConsoleService } from '@app/_services';
import { IrisPayslip, PayRun, TaxYear } from '@app/_models';

@Component({
  selector: 'app-payroll',
  imports: [AsyncPipe, JsonPipe, ReactiveFormsModule],
  templateUrl: './payroll.component.html',
  styleUrl: './payroll.component.css',
})
export class PayrollComponent {
  form!: FormGroup;
  payruns$: Observable<PayRun[]>;
  taxyears$: Observable<TaxYear[]>;
  payslips: IrisPayslip[] = [];

  private employerID: string = environment.staffologyEmployerID;

  private formBuilder = inject(FormBuilder);
  private consoleService = inject(ConsoleService);
  private grossToNetService = inject(GrossToNetService);
  private payRunService = inject(PayRunService);
  private taxYearService = inject(TaxYearService);

  constructor() {
    this.payruns$ = of([]);
    this.taxyears$ = this.taxYearService.getAll();
  }

  ngOnInit(): void {
    this.form = this.formBuilder.group({
      taxYear: [null, Validators.required],
      month: [null, Validators.required],
      sortBy: [null],
      sortDescending: [false],
    });

    this.form.controls['taxYear'].valueChanges.subscribe((value) => {
      this.payruns$ = this.payRunService.getAll(this.employerID, value);
    });

    this.consoleService.consoleMessage$.subscribe((message) => {
      console.log(message);
    } );
  }

  get f() {
    return this.form.controls;
  }

  onSubmit() {
    if (this.form.valid) {
      this.grossToNetService
        .getAll(this.employerID, this.f['taxYear'].value, this.f['month'].value, this.f['sortBy'].value, this.f['sortDescending'].value)
        .subscribe((data: IrisPayslip[]) => {
          this.payslips = data;
          this.consoleService.sendPayslipsToConsole(this.payslips);
        });
    }
  }
}
