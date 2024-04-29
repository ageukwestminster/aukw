import { Component, inject } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';

import { tap } from 'rxjs/operators';

import { SalesChartData } from '@app/_models';
import { SummaryService } from '@app/_services';
import { AbstractChartReportComponent } from '../chart-report.component';
import { SharedModule } from '@app/shared/shared.module';

@Component({
  templateUrl: './sales-list.component.html',
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink, SharedModule],
})
export class SalesListComponent extends AbstractChartReportComponent<SalesChartData> {

  private summaryService = inject(SummaryService);

  refreshSummary() {
    this.summaryService
      .getSalesChartData()
      .pipe(
        tap({
          next: (result) => {
            this.data = result;
          },
          error: (error) => {
            console.log(error);
          },
        }),
      )
      .subscribe();
  }

  onRowSelected(takingsID: number | null) {
    if (takingsID) {
      this.router.navigate([`takings/edit/${takingsID}`]);
    }
  }
}
