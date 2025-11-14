import { Component, inject, OnInit } from '@angular/core';
import {
  Router,
  ActivatedRoute,
  Event,
  NavigationEnd,
  NavigationError,
} from '@angular/router';

import { EmployeeName } from '@app/_models';
import { AddEditEmployeeAllocationsComponent } from './add-edit-employee-allocations.component';

@Component({
  imports: [AddEditEmployeeAllocationsComponent],
  template: ` <add-edit-employee-allocations
    [payrollNumber]="payrollNumber"
    [employeeName]="employeeName"
    (allocationsSaved)="onAllocationsSaved()"
  >
  </add-edit-employee-allocations>`,
  standalone: true,
})
export class AddEditAllocationsComponent implements OnInit {
  /** From the Staffology payroll numbers. This will be
   * null if the employee has not yet been added to QBO */
  payrollNumber: number | null = null;
  /** This will only be non-null if the employee has already been added
   * to QuickBooks but not yet assigned allocations.
   */
  employeeName: EmployeeName | null = null; // Maybe not needed????

  private router = inject(Router);
  private route = inject(ActivatedRoute);

  ngOnInit(): void {
    var x = this.route.snapshot;
    if (x) {
      var p = x.params;
      if (p) {
        var pn = p['id'];
        if (pn) {
          this.payrollNumber = Number(pn);
        }
      }
    }
    //const payrollNumber = Number(this.route.snapshot.params['id']);
    //this.payrollNumber = isNaN(payrollNumber) ? null : payrollNumber;

    this.route.paramMap.subscribe((params) => {
      const payrollNumber = Number(params.get('id'));
      this.payrollNumber = isNaN(payrollNumber) ? null : payrollNumber;
      console.log(
        'AddEditAllocationsComponent FROM_ROute payrollNumber:',
        this.payrollNumber,
      );
    });

    console.log(
      'AddEditAllocationsComponent initialized with payrollNumber:',
      this.payrollNumber,
    );
  }

  onAllocationsSaved() {
    //this.router.navigate(['../'], { relativeTo: this.route });
  }
}
