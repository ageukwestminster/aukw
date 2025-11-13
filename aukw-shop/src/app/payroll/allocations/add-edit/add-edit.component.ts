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
  FormArray,
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';
import { Observable, of, switchMap } from 'rxjs';
import {
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

  ngOnInit() {
    this.formMode = FormMode.Add;
    this.form = this.formBuilder.group({
      projects: new FormArray([]), // Populated later
      quickbooksId: [null],
      payrollNumber: [null, Validators.required],
      firstName: [null, Validators.required],
      lastName: [null, Validators.required],
    });

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
        if (payrollNumber) {
          this.formMode = FormMode.Edit;

          this.disableNameFormControls();

          this.completeFormUsingPayrollNumber(payrollNumber);
        } else {
          // Add one blank Allocation
          this.onAddAllocation();
        }
      });

    this.router.events.subscribe((event: Event) => {
      if (event instanceof NavigationEnd) {
        const payrollNumber = Number(this.route.snapshot.params['id']);
        if (payrollNumber) {
          this.completeFormUsingPayrollNumber(payrollNumber);
        }
      }
      if (event instanceof NavigationError) {
        // Present error to user
        console.log(event.error);
      }
    });
  }

  disableNameFormControls() {
    this.form = this.formBuilder.group({
      projects: new FormArray([]),
      quickbooksId: [null, Validators.required],
      payrollNumber: [null, Validators.required],
      // If in edit mode then disable the 'name' controls of the form
      // Can't change Employee's name using this Form, only add New
      firstName: [{ value: null, disabled: true }, Validators.required],
      lastName: [{ value: null, disabled: true }, Validators.required],
    });
  }

  completeFormUsingPayrollNumber(payrollNumber: number) {
    const employee = this.allEmployeeAllocs.find(
      (ea) => ea.name.payrollNumber === payrollNumber,
    );
    if (employee && this.formMode == FormMode.Edit) {
      this.form.patchValue({
        quickbooksId: employee.name.quickbooksId,
        payrollNumber: employee.name.payrollNumber,
        firstName: employee.name.firstName,
        lastName: employee.name.lastName,
      });
      this.clearProjectAllocationsArray();
      employee.projects.forEach((project) => {
        this.addAllocationToArray(project.percentage, project.classID);
      });
    }
  }

  onSubmit() {
    this.submitted = true;
    if (this.formMode == FormMode.Edit) {
      this.loading = true;
      this.allocationsService
        .deleteEmployeeAllocations(this.f['payrollNumber'].value)
        .pipe(
          switchMap(() => {
            return this.allocationsService.append(
              this.convertAllocationsToAllocationArray(),
            );
          }),
          switchMap(() =>
            this.allocationsService.getAllocations(this.employees),
          ),
        )
        .subscribe({
          next: () => {
            this.alertService.success('Employee allocations saved.', {
              keepAfterRouteChange: true,
            });
            this.router.navigate(['../'], { relativeTo: this.route });
          },
          error: (error) => {
            this.alertService.error(
              'Employee allocations not saved. ' + error,
              {
                autoClose: false,
              },
            );
          },
        })
        .add(() => (this.loading = false));
    }
  }

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
}
