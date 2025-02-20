import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';

import { tap } from 'rxjs/operators';

import { SalesChartData } from '@app/_models';
import { ReportService } from '@app/_services';
import { AbstractChartReportComponent } from '../chart-report.component';
import { SalesChartComponent } from '@app/shared';

@Component({
  templateUrl: './sales-list.component.html',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterLink, SalesChartComponent],
})
export class SalesListComponent extends AbstractChartReportComponent<SalesChartData> {
  private reportService = inject(ReportService);

  override refreshSummary() {
    this.reportService
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
