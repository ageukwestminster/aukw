import { Component, inject, Input } from '@angular/core';
import { NgbActiveOffcanvas } from '@ng-bootstrap/ng-bootstrap';

import { EmployeeName } from '@app/_models';
import { AddEditEmployeeAllocationsComponent } from './add-edit-employee-allocations/add-edit-employee-allocations.component';

@Component({
  imports: [AddEditEmployeeAllocationsComponent],
  template: ` <div class="offcanvas-header">
      <h5 class="offcanvas-title">Add New Employee</h5>
      <button
        type="button"
        class="btn-close text-reset"
        aria-label="Close"
        (click)="activeOffcanvas.dismiss('Cross click')"
      ></button>
    </div>
    <div class="offcanvas-body">
      <add-edit-employee-allocations
        [payrollNumber]="payrollNumber"
        [employeeName]="employeeName"
        (allocationsSaved)="activeOffcanvas.close('Allocations Saved')"
      >
      </add-edit-employee-allocations>
    </div>`,
  standalone: true,
})
export class AddEditOffcanvasComponent {
  activeOffcanvas = inject(NgbActiveOffcanvas);

  /** From the Staffology payroll numbers. This will be
   * null if the employee has not yet been added to QBO */
  @Input() payrollNumber: number | null = null;
  /** This will only be non-null if the employee has already been added
   * to QuickBooks but not yet assigned allocations.
   */
  @Input() employeeName: EmployeeName | null = null; // Maybe not needed????
}
