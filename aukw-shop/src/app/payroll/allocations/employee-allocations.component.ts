import { Component, inject, OnInit } from '@angular/core';
import { AsyncPipe, NgFor, NgIf } from '@angular/common';
import { PayrollProcessState } from '@app/_models';
import {
  AlertService,
  LoadingIndicatorService,
  PayrollProcessStateService,
  QBPayrollService,
} from '@app/_services';
import { shareReplay } from 'rxjs';

@Component({
  selector: 'employee-allocations',
  standalone: true,
  imports: [AsyncPipe, NgFor, NgIf],
  templateUrl: './employee-allocations.component.html',
  styleUrl: './employee-allocations.component.css',
})
export class EmployeeAllocationsComponent implements OnInit {
  /** Used for allocations$ Observable */
  public qbPayrollService = inject(QBPayrollService);

  private alertService = inject(AlertService);
  private payrollProcessStateService = inject(PayrollProcessStateService);
  private loadingIndicatorService = inject(LoadingIndicatorService);

  constructor() {}

  ngOnInit() {
    this.qbPayrollService
      .getAllocations()
      .pipe(
        this.loadingIndicatorService.createObserving({
          loading: () => 'Querying Quickbooks for project allocations.',
          success: () => 'Allocations retrieved.',
          error: (err) => `${err}`,
        }),
        shareReplay(1),
      )
      .subscribe({
        error: (error: any) => {
          this.alertService.error(error, {
            autoClose: false,
            keepAfterRouteChange: true,
          });
        },
        complete: () => {
          this.payrollProcessStateService.setState(
            PayrollProcessState.ALLOCATIONS,
          );
        },
      });
  }
}
