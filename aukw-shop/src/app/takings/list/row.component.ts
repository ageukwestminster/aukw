import { Component, EventEmitter, Input, Output } from '@angular/core';
import { formatDate } from '@angular/common';
import { environment } from '@environments/environment';
import { TakingsSummary, User } from '@app/_models';
import { TakingsService, AlertService } from '@app/_services';
/**
 * @TakingsRow: A component for the view of single daily Takings item
 */
@Component({
  selector: 'tr[takings-row]',
  templateUrl: './row.component.html',
})
export class TakingsRowComponent {
  @Input() takings!: TakingsSummary;
  @Input() user!: User;
  @Output() onTakingsDeleted: EventEmitter<TakingsSummary>;
  @Output() onTakingsAddedToQB: EventEmitter<TakingsSummary>;

  constructor(
    private takingsService: TakingsService,
    private alertService: AlertService,
  ) {
    this.onTakingsDeleted = new EventEmitter();
    this.onTakingsAddedToQB = new EventEmitter();
  }

  get isProduction() {
    return environment.production;
  }

  deleteTakings(e: Event) {
    e.stopPropagation(); // If click propagates it will open the edit member page

    if (!this.takings || !this.takings.id) return;

    this.takings.isDeleting = true;
    this.takingsService.delete(this.takings.id).subscribe(() => {
      this.alertService.success('Takings deleted', {
        keepAfterRouteChange: true,
      });
      this.onTakingsDeleted.emit(this.takings);
    });
  }

  // Add a Sales Receipt to QB based on the taking data in the dB
  addToQuickbooks(e: Event) {
    e.stopPropagation(); // If click propagates it will open the edit member page

    if (!this.takings || !this.takings.id) return;

    this.takings.isUpdating = true;
    this.takingsService
      .addToQuickbooks(this.takings.id) // Adds to QB and sets 'quickbooks' = 1 in dB
      .subscribe({
        next: () => {
          this.alertService.success(
            'Daily sales added to QB for ' +
              formatDate(this.takings.date, 'dd-MMM', 'en_GB'),
            { keepAfterRouteChange: true },
          );
          this.takings.quickbooks = true; // Quickbooks is now updated
          this.takings.isUpdating = false;
          this.onTakingsAddedToQB.emit(this.takings); // refresh screen
        },
        error: (error) => {
          this.alertService.error(
            'Daily sales for ' +
              formatDate(this.takings.date, 'dd-MMM-yy', 'en_GB') +
              ' not added to Quickbooks. Error message: "' +
              error.message +
              '"',
            { autoClose: false },
          );
          this.takings.isUpdating = false;
          this.onTakingsAddedToQB.emit(this.takings); // refresh screen
        },
      });
  }

  // Prevents the click event propagating back up to the table row
  // which would open the edit Takings view
  onClickEvent(e: Event) {
    e.stopPropagation();
  }
}
