import {
  Component,
  EventEmitter,
  inject,
  Input,
  OnInit,
  Output,
} from '@angular/core';
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
  selector: 'add-edit-employee-allocations',
  imports: [AsyncPipe, JsonPipe, NgClass, ReactiveFormsModule],
  templateUrl: './add-edit-employee-allocations.component.html',
  styleUrl: './add-edit-employee-allocations.component.css',
})
export class AddEditEmployeeAllocationsComponent implements OnInit {
  allEmployeeAllocs: EmployeeAllocations[] = [];
  classes: Observable<QBClass[]> = of([]);
  employees: EmployeeName[] = [];
  form!: FormGroup;
  formMode: FormMode = FormMode.Add;
  submitted: boolean = false;
  loading: boolean = false;
  formOptions: AbstractControlOptions;

  private realmID: string = environment.qboCharityRealmID;
  private realmIDEnterprises: string = environment.qboEnterprisesRealmID;

  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private alertService = inject(AlertService);
  private allocationsService = inject(AllocationsService);
  private formBuilder = inject(FormBuilder);
  private qbClassService = inject(QBClassService);
  private qbEmployeeService = inject(QBEmployeeService);

  /** From the Staffology payroll numbers. This will be
   * null if the employee has not yet been added to QBO */
  @Input() payrollNumber: number | null = null;
  /** This will only be non-null if the employee has already been added
   * to QuickBooks but not yet assigned allocations.
   */
  @Input() employeeName: EmployeeName | null = null;

  @Output() allocationsSaved: EventEmitter<EmployeeAllocations> =
    new EventEmitter<EmployeeAllocations>();

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

    this.classes = this.qbClassService.getAllocatableClasses(this.realmID);
    this.allocationsService.allocations$.subscribe(
      (allocations) => (this.allEmployeeAllocs = allocations),
    );

    this.qbEmployeeService
      .getAll(this.realmID)
      .pipe(
        switchMap((employees) => {
          this.employees = employees;
          return this.allocationsService.getAllocations(employees);
        }),
      )
      .subscribe((allocations) => {
        this.allEmployeeAllocs = allocations;

        if (this.payrollNumber) {
          if (this.employeeName && this.employeeName.payrollNumber) {
            const quickbooksId =
              this.employees.find(
                (e) => e.payrollNumber === this.employeeName!.payrollNumber,
              )?.quickbooksId || null;
            this.rebuildFormForAddMode(
              quickbooksId,
              this.employeeName.payrollNumber,
              this.employeeName.firstName,
              this.employeeName.lastName,
            );
            this.onAddAllocation();
          } else {
            this.formMode = FormMode.Edit;

            this.rebuildFormForEditMode();

            const unAllocatedEmployee = this.allEmployeeAllocs.find(
              (ea) => ea.name.payrollNumber === this.payrollNumber,
            )
              ? false
              : true;

            this.patchFormUsingInputParams(
              this.payrollNumber,
              unAllocatedEmployee,
            );
          }
        } else {
          // Add one blank Allocation
          if (this.projects && this.projects.length == 0) {
            this.onAddAllocation();
          }
        }
      });

    this.router.events.subscribe((event: Event) => {
      if (event instanceof NavigationEnd) {
        if (this.payrollNumber) {
          const unAllocatedEmployee = this.allEmployeeAllocs.find(
            (ea) => ea.name.payrollNumber === this.payrollNumber,
          )
            ? false
            : true;

          this.patchFormUsingInputParams(
            this.payrollNumber,
            unAllocatedEmployee,
          );
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

  private rebuildFormForEditMode() {
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

  private rebuildFormForAddMode(
    quickbooksId: number | null,
    payrollNumber: number | null,
    firstName: string | null,
    lastName: string,
  ) {
    this.form = this.formBuilder.group(
      {
        projects: new FormArray([]),
        quickbooksId: [quickbooksId],
        payrollNumber: [
          { value: payrollNumber, disabled: true },
          Validators.required,
          Validators.pattern('^\d+$'),
        ],
        // If in edit mode then disable the 'name' controls of the form
        // Can't change Employee's name using this Form, only add New
        firstName: [{ value: firstName, disabled: true }, Validators.required],
        lastName: [{ value: lastName, disabled: true }, Validators.required],
      },
      this.formOptions,
    );
  }

  /**
   * Using the input parameters, patch the form controls
   * @param payrollNumber The payroll ID of the employee as supplied by the payroll software supplier
   * @param unAllocatedEmployee 'true' if the employee is in QBO but has no allocations
   */
  private patchFormUsingInputParams(
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

  /**
   * Called when the form is submitted
   * @returns
   */
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

    editOrAdd$ =
      this.allocationsService.saveEmployeeAllocations(employeeAllocations);

    editOrAdd$
      .pipe(
        // Reload allocations
        switchMap(() => this.allocationsService.getAllocations(this.employees)),
      )
      .subscribe({
        next: () => {
          this.alertService.success('Employee allocations saved.', {
            keepAfterRouteChange: true,
          });

          // Inform the parent component that allocations have been saved
          // This will allow the offcanvas to be closed
          this.allocationsSaved.emit(
            new EmployeeAllocations({
              name: new EmployeeName({
                quickbooksId: this.f['quickbooksId'].value,
                payrollNumber: this.f['payrollNumber'].value,
                firstName: this.f['firstName'].value,
                lastName: this.f['lastName'].value,
                name:
                  (this.f['firstName'].value ?? '') +
                  ' ' +
                  (this.f['lastName'].value ?? ''),
              }),
              projects: this.convertAllocationsToSimpleArray(),
            }),
          );
        },
        error: (error) => {
          this.alertService.error('Employee allocations not saved. ' + error, {
            autoClose: false,
          });
        },
      })
      .add(() => (this.loading = false));
  }

  /** Add an empty line to the allocations FormArray */
  onAddAllocation() {
    this.addAllocationToArray('', '');
  }

  /**
   * Add a new FormGroup to the projects FormArray
   * @param percentage The percentage allocation
   * @param project The class ID of the project
   */
  private addAllocationToArray(
    percentage: number | '' = '',
    project: string = '',
  ) {
    this.projects.push(
      this.formBuilder.group({
        percentage: [percentage],
        project: [project],
      }),
    );
  }

  /*
   * Remove the FormGroup at the specified index from the projects FormArray
   */
  onRemoveAllocation(index: number) {
    if (this.projects!.length > 1 && index) {
      this.projects!.removeAt(index);
    }
  }

  /**
   * Remove all the existing controls (actually FormGroups) from the form.projects FormArray
   */
  private clearProjectAllocationsArray() {
    const length = this.projects.length;
    if (length) {
      for (let index = 0; index < length; index++) {
        this.projects.removeAt(0);
      }
    }
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
