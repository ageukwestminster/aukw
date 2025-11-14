import { Component, inject } from '@angular/core';
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
  Validators,
} from '@angular/forms';
import { Observable, of, switchMap } from 'rxjs';
import {
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
  imports: [],
  template: '',
})
export abstract class AddEditAllocationsParentComponent {
  allEmployeeAllocs: EmployeeAllocations[] = [];
  classes: Observable<QBClass[]> = of([]);
  employees: EmployeeName[] = [];
  form!: FormGroup;
  formMode: FormMode = FormMode.Add;
  submitted: boolean = false;
  loading: boolean = false;
  formOptions: AbstractControlOptions;

  successFn: (() => void) | null = null;

  protected realmID: string = environment.qboCharityRealmID;

  protected alertService = inject(AlertService);
  protected allocationsService = inject(AllocationsService);
  protected formBuilder = inject(FormBuilder);
  protected qbClassService = inject(QBClassService);
  protected qbEmployeeService = inject(QBEmployeeService);

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

  protected initialize() {
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

    this.allocationsService.allocations$.subscribe(
      (allocations) => (this.allEmployeeAllocs = allocations),
    );
  }

  protected rebuildFormForEditMode() {
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
   * Called when the form is submitted
   * @returns
   */
  onSubmit() {
    this.submitted = true;

    if (!this.form.valid) return;

    this.loading = true;

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

          // CALL ROUTER TO GO BACK TO /allocations/add
          // or
          // EMIT EVENT TO PARENT COMPONENT TO INFORM OF SUCCESSFUL SAVE
          if (this.successFn) this.successFn();
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
  protected addAllocationToArray(
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
  protected clearProjectAllocationsArray() {
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
  protected convertAllocationsToSimpleArray(): {
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
