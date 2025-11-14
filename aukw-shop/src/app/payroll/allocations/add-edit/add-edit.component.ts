import { Component, inject, OnInit } from '@angular/core';
import { AsyncPipe, JsonPipe, NgClass } from '@angular/common';
import {
  Router,
  ActivatedRoute,
  Event,
  NavigationEnd,
  NavigationError,
} from '@angular/router';
import { environment } from '@environments/environment';
import {
  AbstractControlOptions,
  FormArray,
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';
import { Observable, of, switchMap } from 'rxjs';
import {
  ApiMessage,
  EmployeeAllocation,
  EmployeeAllocations,
  EmployeeName,
  FormMode,
  QBClass,
} from '@app/_models';
import {
  AlertService,
  AllocationsService,
  QBClassService,
  QBEmployeeService,
} from '@app/_services';
import { ProjectAllocationsValidater } from '@app/_helpers';

@Component({
  imports: [AsyncPipe, JsonPipe, NgClass, ReactiveFormsModule],
  templateUrl: './add-edit.component.html',
  styleUrl: './add-edit.component.css',
})
export class AllocationsAddEditComponent implements OnInit {
  allEmployeeAllocs: EmployeeAllocations[] = [];
  classes: Observable<QBClass[]> = of([]);
  employees: EmployeeName[] = [];
  payrollNumber!: number;
  form!: FormGroup;
  formMode: FormMode = FormMode.Add;
  submitted: boolean = false;
  loading: boolean = false;
  formOptions: AbstractControlOptions;

  private realmID: string = environment.qboCharityRealmID;

  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private alertService = inject(AlertService);
  private allocationsService = inject(AllocationsService);
  private formBuilder = inject(FormBuilder);
  private qbClassService = inject(QBClassService);
  private qbEmployeeService = inject(QBEmployeeService);

  /** convenience getter for easy access to form fields */
  get f() {
    return this.form.controls;
  }
  /** convenience getter for easy access to projects FormArray */
  get projects() {
    return this.f['projects'] as FormArray;
  }
  /** convenience getter for easy access to form fields within allocations array*/
  get allocationsFormGroups() {
    return this.projects!.controls as FormGroup[];
  }

  constructor() {
    this.formOptions = {
      validators: [ProjectAllocationsValidater('projects')],
    };
  }

  ngOnInit() {
    this.formMode = FormMode.Add;
    this.form = this.formBuilder.group(
      {
        projects: new FormArray([]), // Populated later
        quickbooksId: [null],
        payrollNumber: [
          null,
          [Validators.required, Validators.pattern(/^(0|[1-9]\d*)?$/)],
        ],
        firstName: [null, Validators.required],
        lastName: [null, Validators.required],
      },
      this.formOptions,
    );

    this.classes = this.qbClassService.allocatableClasses$;
    this.allocationsService.allocations$.subscribe(
      (allocations) => (this.allEmployeeAllocs = allocations),
    );

    this.qbEmployeeService.employees$
      .pipe(
        switchMap((employees) => {
          this.employees = employees;
          return this.allocationsService.getAllocations(employees);
        }),
      )
      .subscribe((allocations) => {
        this.allEmployeeAllocs = allocations;

        const payrollNumber = Number(this.route.snapshot.params['id']);
        const unAllocatedEmployee = Boolean(
          this.route.snapshot.queryParams['unallocated'],
        );
        if (payrollNumber) {
          this.formMode = FormMode.Edit;

          this.rebuildFormForEditMode();

          this.patchFormUsingRouteParams(payrollNumber, unAllocatedEmployee);
        } else {
          // Add one blank Allocation
          if (this.projects && this.projects.length == 0) {
            this.onAddAllocation();
          }
        }
      });

    this.router.events.subscribe((event: Event) => {
      if (event instanceof NavigationEnd) {
        const payrollNumber = Number(this.route.snapshot.params['id']);
        const unAllocatedEmployee = Boolean(
          this.route.snapshot.queryParams['unallocated'],
        );
        if (payrollNumber) {
          this.patchFormUsingRouteParams(payrollNumber, unAllocatedEmployee);
        }
      }
      if (event instanceof NavigationError) {
        // Present error to user
        this.alertService.error('Navigation error ocurred. ' + event.error, {
          autoClose: false,
        });
      }
    });
  }

  rebuildFormForEditMode() {
    this.form = this.formBuilder.group(
      {
        projects: new FormArray([]),
        quickbooksId: [null, Validators.required],
        payrollNumber: [
          { value: null, disabled: true },
          Validators.required,
          Validators.pattern('^\d+$'),
        ],
        // If in edit mode then disable the 'name' controls of the form
        // Can't change Employee's name using this Form, only add New
        firstName: [{ value: null, disabled: true }, Validators.required],
        lastName: [{ value: null, disabled: true }, Validators.required],
      },
      this.formOptions,
    );
  }

  /**
   *
   * @param payrollNumber The payroll ID of the employee as supplied by the payroll software supplier
   * @param unAllocatedEmployee 'true' if the employee is in QBO but has no allocations
   */
  patchFormUsingRouteParams(
    payrollNumber: number,
    unAllocatedEmployee: boolean,
  ) {
    if (this.formMode == FormMode.Add) return;

    this.clearProjectAllocationsArray();

    if (unAllocatedEmployee) {
      const employeeName = this.employees.find(
        (e) => e.payrollNumber === payrollNumber,
      );
      this.patchFormUsingEmployeeName(employeeName);
      // Add one blank Allocation
      this.onAddAllocation();
    } else {
      const employeeAllocs = this.allEmployeeAllocs.find(
        (ea) => ea.name.payrollNumber === payrollNumber,
      );
      if (employeeAllocs) {
        this.patchFormUsingEmployeeName(employeeAllocs.name);
        employeeAllocs.projects.forEach((project) => {
          this.addAllocationToArray(project.percentage, project.classID);
        });
      }
    }
  }

  /**
   * Using the information in the supplied EmployeeName object set
   * the values of the form controls
   * @param employeeName
   */
  private patchFormUsingEmployeeName(employeeName: EmployeeName | undefined) {
    if (employeeName) {
      this.form.patchValue({
        quickbooksId: employeeName.quickbooksId,
        payrollNumber: employeeName.payrollNumber,
        firstName: employeeName.firstName,
        lastName: employeeName.lastName,
      });
    }
  }

  onSubmit() {
    this.submitted = true;

    if (!this.form.valid) return;

    this.loading = true;

    var editOrAdd$: Observable<ApiMessage>;

    const employeeAllocations = new EmployeeAllocations({
      name: new EmployeeName({
        quickbooksId: this.f['quickbooksId'].value,
        payrollNumber: this.f['payrollNumber'].value,
        firstName: this.f['firstName'].value,
        lastName: this.f['lastName'].value,
      }),
      projects: this.convertAllocationsToSimpleArray(),
    });

    this.allocationsService
      .saveEmployeeAllocations(employeeAllocations)
      .pipe(
        // Reload allocations
        switchMap(() => this.allocationsService.getAllocations(this.employees)),
      )
      .subscribe({
        next: () => {
          this.alertService.success('Employee allocations saved.', {
            keepAfterRouteChange: true,
          });

          this.router.navigate(['/allocations/add']);
        },
        error: (error) => {
          this.alertService.error('Employee allocations not saved. ' + error, {
            autoClose: false,
          });
        },
      })
      .add(() => (this.loading = false));
  }
/*
    if (this.formMode == FormMode.Edit) {
      // clear any old allocations for this employee in the db
      editOrAdd$ = this.allocationsService
        .deleteEmployeeAllocations(this.f['payrollNumber'].value)
        .pipe(
          // Store allocations in database
          switchMap(() => {
            return this.allocationsService.append(
              this.convertAllocationsToAllocationArray(),
            );
          }),
        );
    } else {
      // Create the new employee in QBO
      editOrAdd$ = this.qbEmployeeService
        .create(this.realmID, {
          givenName: this.f['firstName'].value,
          familyName: this.f['lastName'].value,
          employeeNumber: this.f['payrollNumber'].value,
        })
        .pipe(
          // Store allocations in database
          switchMap((message) => {
            const quickbooksId = message.id;
            this.f['quickbooksId'].setValue(quickbooksId);
            return this.allocationsService.append(
              this.convertAllocationsToAllocationArray(),
            );
          }),
        );
    }

    editOrAdd$
      .pipe(
        // Reload allocations
        switchMap(() => {
          return this.allocationsService.append(
            this.convertAllocationsToAllocationArray(),
          );
        }),
        switchMap(() => this.allocationsService.getAllocations(this.employees)),
      )
      .subscribe({
        next: () => {
          this.alertService.success('Employee allocations saved.', {
            keepAfterRouteChange: true,
          });
          this.router.navigate(['/allocations']);
        },
        error: (error) => {
          this.alertService.error('Employee allocations not saved. ' + error, {
            autoClose: false,
          });
        },
      })
      .add(() => (this.loading = false));
  }*/

  onAddAllocation() {
    this.addAllocationToArray('', '');
  }

  addAllocationToArray(percentage: number | '' = '', project: string = '') {
    this.projects.push(
      this.formBuilder.group({
        percentage: [percentage],
        project: [project],
      }),
    );
  }

  onRemoveAllocation(index: number) {
    if (this.projects!.length > 1 && index) {
      this.projects!.removeAt(index);
    }
  }

  /** Remove all the existing controls (actually FormGroups) from the form.projects FormArray*/
  clearProjectAllocationsArray() {
    const length = this.projects.length;
    if (length) {
      for (let index = 0; index < length; index++) {
        this.projects.removeAt(0);
      }
    }
  }

  convertAllocationsToAllocationArray(): EmployeeAllocation[] {
    return this.allocationsFormGroups.map((element) => {
      return new EmployeeAllocation({
        payrollNumber: this.f['payrollNumber'].value,
        quickbooksId: this.f['quickbooksId'].value,
        isShopEmployee:
          element.controls['project'].value === '1400000000000130700',
        percentage: element.controls['percentage'].value,
        class: element.controls['project'].value,
      });
    });
  }

  /**
   * Convert the allocations FormArray into a simple array of objects
   * @returns
   */
  private convertAllocationsToSimpleArray(): {
    percentage: number;
    classID: string;
  }[] {
    return this.allocationsFormGroups.map((element) => {
      return {
        percentage: Number(element.controls['percentage'].value),
        classID: String(element.controls['project'].value),
      };
    });
  }
}
