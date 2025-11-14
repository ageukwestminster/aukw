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
  template: ` 
      <add-edit-employee-allocations
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
    this.route.queryParamMap.subscribe((params) => {
      const payrollNumParam = params.get('payrollNumber');
      this.payrollNumber = payrollNumParam
        ? parseInt(payrollNumParam, 10)
        : null;
    });
  }

  onAllocationsSaved(){
    this.router.navigate(['../'], { relativeTo: this.route });
  }
}