import { Component, DestroyRef, inject, OnInit } from '@angular/core';
import { AsyncPipe, JsonPipe } from '@angular/common';
import {
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';
import {
  from,
  Observable,
  of,
  map,
  shareReplay,
  switchMap,
  Subject,
  takeUntil,
  tap,
  toArray,
} from 'rxjs';
import { NgbOffcanvas, NgbTooltip } from '@ng-bootstrap/ng-bootstrap';
import { environment } from '@environments/environment';
import {
  GrossToNetService,
  PayRunService,
  TaxYearService,
} from '@app/_services/payroll';
import {
  AlertService,
  ConsoleService,
  LoadingIndicatorService,
  QBEmployeeService,
  QBPayrollService,
} from '@app/_services';
import {
  EmployeeAllocation,
  EmployeeName,
  IrisPayslip,
  PayRun,
  TaxYear,
} from '@app/_models';
import { fromArrayToElement } from '@app/_helpers';
import { PayslipListComponent } from './payslip-list/list.component';
import { PayslipsSummaryComponent } from './payslips-summary/payslips-summary.component';
import { NewEmployeeComponent } from './new-employee/new-employee.component';

@Component({
  selector: 'app-payroll',
  imports: [
    AsyncPipe,
    JsonPipe,
    PayslipListComponent,
    PayslipsSummaryComponent,
    ReactiveFormsModule,
    NgbTooltip,
  ],
  templateUrl: './payroll.component.html',
  styleUrl: './payroll.component.css',
})
export class PayrollComponent implements OnInit {
  form!: FormGroup;
  payruns$: Observable<PayRun[]>;
  taxyears$: Observable<TaxYear[]>;
  payslips: IrisPayslip[] = [];
  allocations: EmployeeAllocation[] = [];
  employees: EmployeeName[] = [];
  payrollDate: string = '';
  total: IrisPayslip = new IrisPayslip();
  payslipsWithMissingEmployeesOrAllocations: IrisPayslip[] = [];
  loading: [boolean, boolean] = [false, false];
  showNextStep: boolean = false;

  private employerID: string = environment.staffologyEmployerID;
  private realmID: string = environment.qboCharityRealmID;

  private formBuilder = inject(FormBuilder);
  private consoleService = inject(ConsoleService);
  private grossToNetService = inject(GrossToNetService);
  private payRunService = inject(PayRunService);
  private taxYearService = inject(TaxYearService);
  private alertService = inject(AlertService);
  /** Used for allocations$ Observable, which is a public property of PayrollService */
  private qbPayrollService = inject(QBPayrollService);
  /** Used to download list of current employee names */
  private qbEmployeeService = inject(QBEmployeeService);
  private destroyRef = inject(DestroyRef);
  private offcanvasService = inject(NgbOffcanvas);
  private loadingIndicatorService = inject(LoadingIndicatorService);

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

    this.loading[0] = true;

    /**
     * This pattern is used to subscribe to an rxjs Subject and automatically
     * unsubscribe when the object is destroyed. Angular gives us the destroyRef
     * hook to manage this.
     * { @link https://medium.com/@chandrashekharsingh25/exploring-the-takeuntildestroyed-operator-in-angular-d7244c24a43e }
     */
    const destroyed = new Subject();
    this.destroyRef.onDestroy(() => {
      destroyed.next('');
      destroyed.complete();
    });

    this.consoleService.consoleMessage$
      .pipe(takeUntil(destroyed))
      .subscribe((message) => {
        console.log(message);
      });

    this.qbPayrollService.allocations$
      .pipe(takeUntil(destroyed))
      .subscribe((allocations) => {
        this.allocations = allocations;
        this.loading[0] = false;
        // DEBUG VALUES
        this.f['month'].setValue(7);
        this.f['taxYear'].setValue('Year2025');
      });

    // Load employee names and allocations
    this.loadEmployeesAndAllocations().subscribe({
      error: (error: any) => {
        this.alertService.error(error, {
          autoClose: false,
          keepAfterRouteChange: true,
        });
      },
    });
  }

  get f() {
    return this.form.controls;
  }

  onSubmit() {
    this.loading = [true, false];
    this.reloadPayslipsFromAPI();
  }

  reloadPayslipsFromAPI() {
    if (this.form.valid) {
      this.grossToNetService
        .getAll(
          this.employerID,
          this.f['taxYear'].value,
          this.f['month'].value,
          this.f['sortBy'].value,
          this.f['sortDescending'].value,
        )
        .pipe(
          tap((payslips: IrisPayslip[]) => {
            this.payslips = payslips;
            this.payrollDate = payslips[0]?.payrollDate || '';
          }),
          fromArrayToElement(), // Convert from Observable<T[]> to Observable<T>

          // Loop through each payslip
          map((payslip: IrisPayslip) => {
            // loop through all payslips and sum the values
            // to form a new "total" payslip and put in class level variable
            this.total = this.total.add(payslip);

            // Check for missing employees and missing allocations
            var employeeName = this.employees.find(
              (emp) => emp.payrollNumber === payslip.payrollNumber,
            );
            if (employeeName) {
              payslip.employeeMissingFromQBO = false;
              payslip.quickbooksId = employeeName!.quickbooksId;
            } else {
              payslip.employeeMissingFromQBO = true;
            }

            const allocations = this.allocations.filter(
              // Note use of '==' instead of '===' because of type difference (string vs number)
              (alloc) => alloc.payrollNumber == payslip.payrollNumber,
            );
            if (allocations && allocations.length) {
              payslip.isShopEmployee = allocations[0].isShopEmployee;
            } else {
              payslip.allocationsMissingFromQBO = true;
            }

            return payslip;
          }),

          toArray(), // Convert back from Observable<T> to Observable<T[]>

          map((payslips: IrisPayslip[]) => {
            this.payslipsWithMissingEmployeesOrAllocations = payslips.filter(
              (payslip) =>
                payslip.employeeMissingFromQBO ||
                payslip.allocationsMissingFromQBO,
            );
            return payslips;
          }),

          // Get payslip flags for Charity QBO ... checking to see if transactions have been entered already
          switchMap((payslips: IrisPayslip[]) => {
            return this.qbPayrollService.payslipFlagsForCharity(
              payslips,
              this.payrollDate,
            );
          }),

          // Get payslip flags for Enterprises QBO
          switchMap((payslips: IrisPayslip[]) => {
            return this.qbPayrollService.payslipFlagsForShop(
              payslips,
              this.payrollDate,
            );
          }),

          // Keep user informed
          this.loadingIndicatorService.createObserving({
            loading: () =>
              ' Querying QuickBooks to see if transactions already entered.',
            success: () => `Successfully loaded QuickBooks transactions.`,
            error: (err) => `${err}`,
          }),
          shareReplay(1),
        )
        .subscribe({
          next: (payslips: IrisPayslip[]) => {
            this.payslips = payslips;
          },
          error: (error: any) => {
            this.alertService.error(error, {
              autoClose: false,
              keepAfterRouteChange: true,
            });
            this.loading = [false, false];
          },
          complete: () => {
            this.loading = [false, false];
            this.showNextStep =
              this.payslipsWithMissingEmployeesOrAllocations &&
              !this.payslipsWithMissingEmployeesOrAllocations.length;
            // DEBUG ONLY: send payslips to console
            //this.consoleService.sendPayslipsToConsole(this.payslips);
          },
        });
    }
  }

  onEmployeeToAdd(payslip: IrisPayslip) {
    const offcanvasRef = this.offcanvasService.open(NewEmployeeComponent);

    // Pass known values to offcanvas component
    offcanvasRef.componentInstance.payrollNumber = payslip.payrollNumber;

    // Pass employee name if not missing
    if (!payslip.employeeMissingFromQBO) {
      offcanvasRef.componentInstance.employeeName = this.employees.find(
        (emp) => emp.payrollNumber === payslip.payrollNumber,
      );
    }

    from(offcanvasRef.result).subscribe(() => {
      this.reloadEverything();
    });
  }

  reloadEverything() {
    this.loading[1] = true;
    this.loadEmployeesAndAllocations().subscribe({
      next: () => {
        this.reloadPayslipsFromAPI();
      },
      error: (error: any) => {
        this.alertService.error(error, {
          autoClose: false,
          keepAfterRouteChange: true,
        });
        this.loading[1] = false;
      },
      complete: () => {
        this.loading[1] = false;
      },
    });
  }

  private loadEmployeesAndAllocations(): Observable<EmployeeAllocation[]> {
    return this.qbEmployeeService.getAll(this.realmID).pipe(
      switchMap((employees: EmployeeName[]) => {
        this.employees = employees;
        return this.qbPayrollService.getAllocations();
      }),
    );
  }

  createQBOEntries() {
    
  }
}
