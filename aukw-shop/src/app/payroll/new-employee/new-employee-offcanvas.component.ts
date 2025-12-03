import { Component, inject, Input } from '@angular/core';
import { NgbActiveOffcanvas } from '@ng-bootstrap/ng-bootstrap';

import { EmployeeName } from '@app/_models';
import { AddEmployeeViaOffCanvasComponent } from '../allocations/add-edit-employee-allocations/add-edit-employee-allocations.component';

@Component({
  imports: [AddEmployeeViaOffCanvasComponent],
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
export class NewEmployeeOffcanvasComponent {
  activeOffcanvas = inject(NgbActiveOffcanvas);

  /** The id used in the Staffology payroll reports */
  @Input() payrollNumber: number | null = null;
  /** Employee name and QuickBooks Id. QuickBooks Id may be null */
  @Input() employeeName: EmployeeName | null = null;
}
