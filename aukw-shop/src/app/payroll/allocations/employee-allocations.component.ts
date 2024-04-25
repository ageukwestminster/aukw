import { Component, EventEmitter, Input, inject, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { retry, shareReplay } from 'rxjs';
import { EmployeeAllocation } from '@app/_models';
import {
  AlertService,
  LoadingIndicatorService,
  QBPayrollService,
} from '@app/_services';

@Component({
  selector: 'employee-allocations',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './employee-allocations.component.html',
  styleUrl: './employee-allocations.component.css',
})
export class EmployeeAllocationsComponent {

  private alertService = inject(AlertService);
  public qbPayrollService = inject(QBPayrollService);
  private loadingIndicatorService = inject(LoadingIndicatorService);

  constructor() {}

  /**
   *
   * @returns
   */
  ngOnInit(): void {

    this.qbPayrollService
      .getAllocations()
      .pipe(
        retry(2),
        this.loadingIndicatorService.createObserving({
          loading: () => 'Loading employee allocations from Quickbooks',
          success: (result) =>
            `Successfully loaded ${result.length} employee allocations`,
          error: (err) => `${err}`,
        }),
        shareReplay(1),
      )
      .subscribe({
        next: () => { },
        error: (error: any) => {
          this.alertService.error(error);
        },
      });
  }
}
