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
    // Set flag to tell user to expect a wait
    this.loading = true;

    this.reportService.getSalesChartData().subscribe({
      next: (result: SalesChartData) => (this.data = result),
      error: (error: any) => {
        this.loading = false;
        this.data = new SalesChartData();
        this.alertService.error(error, { autoClose: false });
      },
      complete: () => (this.loading = false),
    });
  }

  onRowSelected(takingsID: number | null) {
    if (takingsID) {
      this.router.navigate([`takings/edit/${takingsID}`]);
    }
  }
}
