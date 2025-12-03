import {
  Component,
  EventEmitter,
  inject,
  Input,
  OnInit,
  Output,
} from '@angular/core';
import { AsyncPipe, JsonPipe, NgClass } from '@angular/common';
import { Router, Event, NavigationEnd, NavigationError } from '@angular/router';
import { FormArray, ReactiveFormsModule, Validators } from '@angular/forms';
import { switchMap } from 'rxjs';
import { EmployeeAllocations, EmployeeName, FormMode } from '@app/_models';
import { AddEditAllocationsParentComponent } from './parent.component';

@Component({
  selector: 'add-edit-employee-allocations',
  imports: [AsyncPipe, JsonPipe, NgClass, ReactiveFormsModule],
  templateUrl: './add-edit-employee-allocations.component.html',
  styleUrl: './add-edit-employee-allocations.component.css',
})
export class AddEmployeeViaOffCanvasComponent
  extends AddEditAllocationsParentComponent
  implements OnInit
{
  private router = inject(Router);

  /** From the Staffology payroll numbers. This will be
   * null if the employee has not yet been added to QBO */
  @Input() payrollNumber: number | null = null;

  /** This will only be non-null if the employee has already been added
   * to QuickBooks but not yet assigned allocations.
   */
  @Input() employeeName: EmployeeName | null = null;

  @Output() allocationsSaved: EventEmitter<EmployeeAllocations> =
    new EventEmitter<EmployeeAllocations>();

  constructor() {
    super();

    this.successFn = () => {
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
    };
  }

  ngOnInit() {
    this.initialize();

    this.classes = this.qbClassService.getAllocatableClasses(this.realmID);

    // Get a list of employees and their allocations
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
}
