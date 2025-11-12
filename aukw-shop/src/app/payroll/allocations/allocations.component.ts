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
  imports: [NgClass, ReactiveFormsModule],
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
  allocatedEmployees: EmployeeName[] = [];
  mostRecentPayrun: IrisPayslip[] = [];
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
    this.qbClassService
      .getAll(this.realmID)
      .pipe(
        switchMap((classes) => {

          const unrestricted = classes.find(
            (cls) => cls.value.toLowerCase() === '01 unrestricted',
          );
          if (unrestricted) unrestricted.value = 'Charity Shop';

          this.classes = classes.filter(
            (qbClass) => invalidClasses.indexOf(qbClass.value) === -1,
          );

          return this.qbEmployeeService.getAll(this.realmID);
        }),
        switchMap((employees) => {
          this.employees = employees;
          return this.payrunService.getLatest(this.employerID);
        }),

        // Get details of last pay run
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

          return this.qbPayrollService.getAllocations2(
            this.employees,
          );
        }),

        switchMap((allocations) => {
          this.inPayrun = new Map<number, boolean>();

          allocations.forEach((element) => {
            if (
              !this.allocatedEmployees.find(
                (pair) => pair.payrollNumber === element.name.payrollNumber,
              )
            ) {
                this.allocatedEmployees.push(element.name);
                this.inPayrun.set(
                  element.name.payrollNumber,
                  this.isInMostRecentPayrun(element.name.payrollNumber),
                );
            }
          });

          return of(allocations);
        }),
      )
      .subscribe({
        next: (value) => this.allocations = value,
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
      this.allocatedEmployees = this.allocatedEmployees.filter(
        (x) => x.payrollNumber != employee.payrollNumber,
      );
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

  summarizeProjects(en : EmployeeName) : string {

    var ea : EmployeeAllocations|undefined = this.allocations.find(a => a.name.payrollNumber === en.payrollNumber);
    
    // Projects IS NULL !!
    if (!ea || !ea.projects || !ea.projects.length) return '';

    var output: string = ''
    var count: number = 0;

    ea.projects.forEach(element => {
      var cls = this.classes.find(clz => clz.id === element.classID);
      if (cls) {
        output.concat(count?', ':''+cls.shortName);
      } else {
        output.concat(count?', ':''+'Unknown Project')
      }
      count++;
    });

    return output;
  }
}
