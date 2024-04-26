import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { QBPayrollService } from '@app/_services';

@Component({
  selector: 'employee-allocations',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './employee-allocations.component.html',
  styleUrl: './employee-allocations.component.css',
})
export class EmployeeAllocationsComponent {
  /** Used for allocations$ Observable */
  public qbPayrollService = inject(QBPayrollService);

  constructor() {}
}
