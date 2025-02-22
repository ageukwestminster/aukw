import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, NgIf } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { environment } from '@environments/environment';
import { ReportService } from '@app/_services';
import { AbstractChartReportComponent } from '../chart-report.component';
import { DateRangeEnum, SalesByDepartment } from '@app/_models';
import { DateRangeChooserComponent } from '@app/shared';

@Component({
  standalone: true,
  imports: [
    CommonModule,
    DateRangeChooserComponent,
    NgIf,
    ReactiveFormsModule,
    RouterLink,
  ],
  templateUrl: './sales-by-department.component.html',
  styleUrl: './sales-by-department.component.css',
})
export class SalesByDepartmentComponent
  extends AbstractChartReportComponent<SalesByDepartment>
  implements OnInit
{
  private reportService = inject(ReportService);

  override ngOnInit() {
    this.form = this.formBuilder.group({
      dateRange: [DateRangeEnum.LAST_QUARTER],
      startDate: [null],
      endDate: [null],
    });

    this.onDateRangeEnumSelected(DateRangeEnum.LAST_QUARTER);
  }

  override refreshSummary(startDate: string, endDate: string) {
    this.loading = true;
    this.reportService
      .getSalesByDepartment(startDate, endDate, environment.HARROWROAD_SHOPID)
      .subscribe({
        next: (response) => (this.data = response),
        error: (error: any) => {
          this.loading = false;
          this.data = new SalesByDepartment();
          this.alertService.error(error, { autoClose: false });
        },
        complete: () => (this.loading = false),
      });
  }
}
