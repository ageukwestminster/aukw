import { Component, EventEmitter, Input, Output } from '@angular/core';
import { Takings } from '../_models';
import { TakingsService, AlertService } from '@app/_services';
/**
 * @TakingsRow: A component for the view of single daily Takings item
 */
@Component({
  selector: 'tr[takings-row]',
  templateUrl: './row.component.html',
})
export class TakingsRowComponent {

  @Input() takings!: Takings;
  @Output() onTakingsDeleted: EventEmitter<Takings>;

  constructor(
    private takingsService: TakingsService,
    private alertService: AlertService
  ) {
    this.onTakingsDeleted = new EventEmitter();
  }

  deleteTakings(e: Event) {
    e.stopPropagation(); // If click propagates it will open the edit member page

    if (!this.takings || !this.takings.id) return;

    this.takings.isDeleting = true;
    this.takingsService
      .delete(this.takings.id)
      .subscribe(() => {
        this.alertService.success('Takings deleted', {
          keepAfterRouteChange: true,
        });
        this.onTakingsDeleted.emit(this.takings);
      });
  }

  // Prevents the click event propagating back up to the table row
  // which would open the edit Takings view
  onClickEvent(e: Event) {
    e.stopPropagation();
  }

}
