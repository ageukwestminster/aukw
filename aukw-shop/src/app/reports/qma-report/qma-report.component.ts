import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, NgIf } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import {
  NgbAccordionModule,
  NgbDatepickerModule,
} from '@ng-bootstrap/ng-bootstrap';
import { QBReportService } from '@app/_services';
import { AbstractChartReportComponent } from '../chart-report.component';
import { DateRange, DateRangeEnum, InStoreSalesData } from '@app/_models';
import { DateRangeChooserComponent } from '@app/shared';

@Component({
  standalone: true,
  imports: [
    CommonModule,
    DateRangeChooserComponent,
    NgbAccordionModule,
    NgbDatepickerModule,
    NgIf,
    ReactiveFormsModule,
    RouterLink,    
  ],
  templateUrl: './qma-report.component.html',
  styleUrl: './qma-report.component.css',
})
export class QmaReportComponent
  extends AbstractChartReportComponent<InStoreSalesData>
  implements OnInit
{
  private reportService = inject(QBReportService);

  override ngOnInit() {
    this.form = this.formBuilder.group({
      dateRange: [DateRangeEnum.LAST_QUARTER],
      startDate: [null],
      endDate: [null],
    });

    this.onDateRangeChanged(DateRangeEnum.LAST_QUARTER);
  }

  dateRangeChanged(dateRange: DateRange) {
    this.refreshSummary(dateRange.startDate, dateRange.endDate);
  }

  override refreshSummary(startDate: string, endDate: string) {
    this.loading = true;
    this.reportService.getInStoreSales(startDate, endDate).subscribe({
      next: (response) => (this.data = response),
      error: (error: any) => {
        this.loading = false;
        this.data = new InStoreSalesData();
        this.alertService.error(error, { autoClose: false });
      },
      complete: () => (this.loading = false),
    });
  }
}
