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
  allocations: EmployeeAllocation[] = [];
  /**
   * The id of the Quickbooks company. We query QBO for the allocation info.
   */
  @Input() realmId: string = '';
  /**
   * When the allocations have been loaded we will emit them
   */
  @Output() onAllocationsLoaded = new EventEmitter<EmployeeAllocation[]>();

  private alertService = inject(AlertService);
  private qbPayrollService = inject(QBPayrollService);
  private loadingIndicatorService = inject(LoadingIndicatorService);

  /**
   *
   * @returns
   */
  ngOnInit(): void {
    if (!this.realmId) return;
    this.qbPayrollService
      .getAllocations(this.realmId)
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
        next: (result) => {
          this.allocations = result;
          this.onAllocationsLoaded.emit(result);
        },
        error: (error: any) => {
          this.alertService.error(error);
        },
      });
  }
}
