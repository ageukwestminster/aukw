import { Component, inject, OnInit } from '@angular/core';
import { Location, NgClass } from '@angular/common';
import {
  AbstractControlOptions,
  FormArray,
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';
import { RouterLink, RouterOutlet } from '@angular/router';
import { forkJoin, of, switchMap, tap } from 'rxjs';
import { environment } from '@environments/environment';
import {
  AlertService,
  AllocationsService,
  GrossToNetService,
  PayRunService,
  QBClassService,
  QBEmployeeService,
  QBPayrollService,
} from '@app/_services';
import {
  EmployeeAllocation,
  EmployeeAllocations,
  EmployeeName,
  IrisPayslip,
  QBClass,
} from '@app/_models';

@Component({
  selector: 'app-allocations',
  imports: [NgClass, ReactiveFormsModule, RouterLink, RouterOutlet],
  templateUrl: './allocations.component.html',
  styleUrl: './allocations.component.css',
})
export class AllocationsComponent implements OnInit {
  form!: FormGroup;
  classes: QBClass[] = [];
  employees: EmployeeName[] = [];
  allocations: EmployeeAllocations[] = [];
  loading: boolean = false;
  submitted: boolean = false;
  employeesWithAllocations: EmployeeName[] = [];
  inPayrun: Map<number, boolean> = new Map<number, boolean>();

  private realmID: string = environment.qboCharityRealmID;
  private employerID: string = environment.staffologyEmployerID;

  private formBuilder = inject(FormBuilder);
  private alertService = inject(AlertService);
  private allocationsService = inject(AllocationsService);
  private qbClassService = inject(QBClassService);
  private qbEmployeeService = inject(QBEmployeeService);
  private qbPayrollService = inject(QBPayrollService);
  private payrunService = inject(PayRunService);
  private grosstonetService = inject(GrossToNetService);
  private location = inject(Location);

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

    // Start initializing the component by downloading lsits of
    //  i) QBO classes; and
    // ii) QBO Employees
    forkJoin({
      employees: this.qbEmployeeService.getAll(this.realmID),
      classes: this.qbClassService.getAllocatableClasses(this.realmID),
    })
      .pipe(
        switchMap((x) => {
          this.classes = x.classes;
          this.employees = x.employees;

          // Get metadata about the most recent closed Pay Run
          // An 'Open' pay run might not yet have employees allocated to it
          return this.payrunService.getLatest(this.employerID);
        }),

        switchMap((payrun) => {
          return forkJoin({
            // Get full details of that last pay run.
            // This will be used to see if some employees can be deleted
            // from the allocations table
            grossToNet: this.grosstonetService.getAll(
              this.employerID,
              payrun.taxYear,
              payrun.taxMonth,
              null,
              null,
              false,
            ),
            // Get the full list of Employees
            allocations: this.allocationsService.getAllocations(this.employees),
          });
        }),

        switchMap((x) => {
          x.allocations.forEach((element) => {
            this.assignEmployeeByAllocationStatus(element.name, x.grossToNet);
          });
          return of(x.allocations);
        }),
      )
      .subscribe({
        next: (value) => (this.allocations = value),
        error: (e) => {
          this.alertService.error(e, { autoClose: false });
          this.loading = false;
        },
        complete: () => (this.loading = false),
      });
  }

  /**
   * From the list of all QBO employees, find thsoe that actually have
   * project allocations. Of those, identify those that are missing
   * from the last pay run.
   * @param employee
   * @param grossToNetPayslips
   */
  private assignEmployeeByAllocationStatus(
    employee: EmployeeName,
    grossToNetPayslips: IrisPayslip[],
  ) {
    if (
      !this.employeesWithAllocations.find(
        (pair) => pair.payrollNumber === employee.payrollNumber,
      )
    ) {
      this.employeesWithAllocations.push(employee);

      // Identify the employees who were not in the last payrun
      this.identifyMissingEmployeesInLastPayrun(
        employee.payrollNumber,
        grossToNetPayslips,
      );
    }
  }

  /**
   * Identify the employees who were not in the last payrun and
   * place them into a component-level boolean-valued Map called inPayrun
   * @param payrollNumber
   * @param grossToNetPayslips
   */
  private identifyMissingEmployeesInLastPayrun(
    payrollNumber: number,
    grossToNetPayslips: IrisPayslip[],
  ) {
    this.inPayrun.set(
      payrollNumber,
      grossToNetPayslips.some(
        (payslip) => payslip.payrollNumber === payrollNumber,
      ),
    );
  }

  onEditEmployee(employee: EmployeeName) {
    console.log(employee);
  }

  onRemoveEmployee(employee: EmployeeName) {
    if (employee && employee.payrollNumber) {
      this.employeesWithAllocations = this.employeesWithAllocations.filter(
        (x) => x.payrollNumber != employee.payrollNumber,
      );
    }
  }

  onSubmit() {}

  summarizeProjects(en: EmployeeName): string {
    var ea: EmployeeAllocations | undefined = this.allocations.find(
      (a) => a.name.payrollNumber === en.payrollNumber,
    );

    if (!ea || !ea.projects || !ea.projects.length) return '';

    var output: string = '';
    var count: number = 0;

    ea.projects.forEach((element) => {
      var cls = this.classes.find((clz) => clz.id === element.classID);
      if (cls) {
        output = output + (count ? ', ' : '') + cls.shortName;
      } else {
        output = output + (count ? ', ' : '') + 'Unknown Project';
      }
      count++;
    });

    return output;
  }

  employeeProjects(en: EmployeeName): string[] {
    var ea: EmployeeAllocations | undefined = this.allocations.find(
      (a) => a.name.payrollNumber === en.payrollNumber,
    );

    // Projects IS NULL !!
    if (!ea || !ea.projects || !ea.projects.length) return [];

    var output: string[] = [];

    return ea.projects.map((element) => {
      var cls = this.classes.find((clz) => clz.id === element.classID);
      if (cls) {
        return cls.shortName;
      } else {
        return 'Unknown Project';
      }
    });
  }

  reload() {
    this.allocationsService
      .getAllocations(this.employees)
      .pipe(
        switchMap((allocations) => {
          this.employeesWithAllocations = [];

          allocations.forEach((element) => {
            if (
              !this.employeesWithAllocations.find(
                (pair) => pair.payrollNumber === element.name.payrollNumber,
              )
            ) {
              this.employeesWithAllocations.push(element.name);
            }
          });
          return of();
        }),
      )
      .subscribe();
  }

  /** Return to previous page */
  goBack() {
    this.location.back();
    return false; // don't propagate event
  }
}
