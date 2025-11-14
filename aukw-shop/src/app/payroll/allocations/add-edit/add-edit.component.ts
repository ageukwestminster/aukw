import { Component, inject, OnInit } from '@angular/core';
import { AsyncPipe, JsonPipe, NgClass } from '@angular/common';
import {
  Router,
  ActivatedRoute,
  Event,
  NavigationEnd,
  NavigationError,
} from '@angular/router';
import { ReactiveFormsModule } from '@angular/forms';
import { switchMap } from 'rxjs';
import { EmployeeName, FormMode } from '@app/_models';
import { AddEditAllocationsParentComponent } from '../add-edit-employee-allocations/parent.component';

@Component({
  imports: [AsyncPipe, JsonPipe, NgClass, ReactiveFormsModule],
  templateUrl: './add-edit.component.html',
  styleUrl: './add-edit.component.css',
})
export class AllocationsAddEditComponent
  extends AddEditAllocationsParentComponent
  implements OnInit
{
  private route = inject(ActivatedRoute);
  private router = inject(Router);

  constructor() {
    super();

    this.successFn = () => {
      this.router.navigate(['/allocations/add']);
    };
  }

  ngOnInit() {
    this.initialize();

    this.classes = this.qbClassService.allocatableClasses$;

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
}
