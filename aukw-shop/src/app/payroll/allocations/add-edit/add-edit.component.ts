import { Component, inject, OnInit } from '@angular/core';
import { AsyncPipe, NgClass } from '@angular/common';
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
  EmployeeAllocations,
  EmployeeName,
  FormMode,
  QBClass,
} from '@app/_models';
import {
  AllocationsService,
  QBClassService,
  QBEmployeeService,
} from '@app/_services';
import { AllocationRowComponent } from '../allocation-row/allocation-row.component';

@Component({
  imports: [AllocationRowComponent, AsyncPipe, NgClass, ReactiveFormsModule],
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

        const payrollNumber = Number(this.route.snapshot.params['id']);
        if (payrollNumber) {
          this.formMode = FormMode.Edit;

          this.completeFormFromRouteId(payrollNumber);
        } else {
          this.onAddAllocation();
        }
      });

    this.router.events.subscribe((event: Event) => {
      if (event instanceof NavigationEnd) {
        const payrollNumber = Number(this.route.snapshot.params['id']);
        if (payrollNumber) {
          this.completeFormFromRouteId(payrollNumber);
        }
      }
      if (event instanceof NavigationError) {
        // Present error to user
        console.log(event.error);
      }
    });
  }

  completeFormFromRouteId(payrollNumber: number) {
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

  onSubmit() {}

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
  clearProjectAllocationsArray() {
    const length = this.projects.length;
    if (length) {
      for (let index = 0; index < length; index++) {
        this.projects!.removeAt(0);
      }
    }
  }
}
