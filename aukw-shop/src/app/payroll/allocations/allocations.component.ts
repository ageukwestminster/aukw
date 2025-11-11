import { Component, inject, OnInit } from '@angular/core';
import { NgClass } from '@angular/common';
import {
  AbstractControlOptions,
  FormArray,
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';
import { Observable, of, switchMap, tap } from 'rxjs';
import { environment } from '@environments/environment';
import {
  AlertService,
  AllocationsService,
  GrossToNetService,
  PayRunService,
  QBEmployeeService,
  QBEntityService,
  QBPayrollService,
} from '@app/_services';
import {
  EmployeeAllocation,
  EmployeeName,
  ValueStringIdPair,
  ApiMessage,
  ValueIdPair,
  IrisPayslip,
} from '@app/_models';
import { fromArrayToElement } from '@app/_helpers';

@Component({
  selector: 'app-allocations',
  imports: [NgClass, ReactiveFormsModule],
  templateUrl: './allocations.component.html',
  styleUrl: './allocations.component.css',
})
export class AllocationsComponent implements OnInit {
  form!: FormGroup;
  classes: ValueStringIdPair[] = [];
  employees: EmployeeName[] = [];
  allocations: EmployeeAllocation[] = [];
  loading: boolean = false;
  submitted: boolean = false;
  allocatedEmployees: EmployeeName[] = [];
  mostRecentPayrun: IrisPayslip[] = [];
  inPayrun: Map<number, boolean> = new Map<number, boolean>();

  private realmID: string = environment.qboCharityRealmID;
  private employerID: string = environment.staffologyEmployerID;

  private formBuilder = inject(FormBuilder);
  private alertService = inject(AlertService);
  private allocationsService = inject(AllocationsService);
  private qbEntityService = inject(QBEntityService);
  private qbEmployeeService = inject(QBEmployeeService);
  private qbPayrollService = inject(QBPayrollService);
  private payrunService = inject(PayRunService);
  private grosstonetService = inject(GrossToNetService);

  /** convenience getter for easy access to form fields */
  get f() {
    return this.form.controls;
  }
  /** convenience getter for easy access to allocations FormArray */
  get allocs() {
    return this.f['allocations'] as FormArray;
  }

  constructor() {}

  ngOnInit(): void {
    const formOptions: AbstractControlOptions = {
      //validators: [ProjectAllocationsValidater('allocations')],
    };

    this.form = this.formBuilder.group(
      {
        allocs: new FormArray([]),
      },
      formOptions,
    );

    this.loading = true;

    const invalidClasses = [
      'AFL',
      'EOC',
      '02 Designated Funds',
      '03 Restricted',
    ];
    this.qbEntityService
      .getAllClasses(this.realmID)
      .pipe(
        switchMap((classes) => {
          this.classes = classes.filter(
            (qbClass) => invalidClasses.indexOf(qbClass.value) === -1,
          );
          const unrestricted = this.classes.find(
            (cls) => cls.value === '01 unrestricted',
          );
          if (unrestricted) unrestricted.value = 'Charity Shop';
          return this.qbEmployeeService.getAll(this.realmID);
        }),
        switchMap((employees) => {
          this.employees = employees;
          return this.payrunService.getLatest(this.employerID);
        }),

        switchMap((payrun) => {
          return this.grosstonetService.getAll(
            this.employerID,
            payrun.taxYear,
            payrun.taxMonth,
            null,
            null,
            false,
          );
        }),

        switchMap((grossToNetReport) => {
          this.mostRecentPayrun = grossToNetReport;

          return this.qbPayrollService.getAllocations(
            this.classes,
            this.employees,
          );
        }),

        switchMap((allocations) => {
          this.inPayrun = new Map<number, boolean>();

          allocations.forEach((element) => {
            if (
              !this.allocatedEmployees.find(
                (pair) => pair.payrollNumber === element.payrollNumber,
              )
            ) {
              const name = this.employees.find(
                (emp) => emp.payrollNumber === element.payrollNumber,
              );
              if (name) {
                this.allocatedEmployees.push(name);
                this.inPayrun.set(
                  name.payrollNumber,
                  this.isInMostRecentPayrun(name.payrollNumber),
                );
              }
            }
          });

          return of(allocations);
        }),
      )
      .subscribe({
        next: (allocations) => (this.allocations = allocations),
        error: (e) => {
          this.alertService.error(e, { autoClose: false });
          this.loading = false;
        },
        complete: () => (this.loading = false),
      });
  }

  onAddAllocation() {
    this.addAllocationToArray(null);
  }

  addAllocationToArray(employeeAllocation: EmployeeAllocation | null = null) {
    if (!employeeAllocation) {
      this.allocs.push(
        this.formBuilder.group({
          percentage: [''],
          project: [''],
        }),
      );
    }
  }

  onRemoveAllocation(employee: EmployeeName) {
    if (employee && employee.payrollNumber) {
      this.allocatedEmployees = this.allocatedEmployees.filter((x) => x.payrollNumber != employee.payrollNumber);
    }
  }

  isInMostRecentPayrun(payrollNumber: number): boolean {
    if (
      !payrollNumber ||
      !this.mostRecentPayrun ||
      !this.mostRecentPayrun.length
    ) {
      return false;
    } else {
      return this.mostRecentPayrun.some(
        (payslip) => payslip.payrollNumber === payrollNumber,
      );
    }
  }

  onSubmit() {}
}
