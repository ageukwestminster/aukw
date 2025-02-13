import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, NgIf, NgClass } from '@angular/common';
import { FormGroup, ReactiveFormsModule } from '@angular/forms';
import {
  NgbAccordionModule,
  NgbDatepickerModule,
} from '@ng-bootstrap/ng-bootstrap';
import { RouterLink } from '@angular/router';
import { QBReportService } from '@app/_services';
import { AbstractChartReportComponent } from '../chart-report.component';
import { DateRangeEnum, ProfitAndLossData } from '@app/_models';

@Component({
  standalone: true,
  imports: [
    CommonModule,
    NgbAccordionModule,
    NgbDatepickerModule,
    NgIf,
    RouterLink,
    ReactiveFormsModule,
  ],
  templateUrl: './pnl-report.component.html',
  styleUrl: './pnl-report.component.css'
})
export class PnlReportComponent 
  extends AbstractChartReportComponent<ProfitAndLossData>
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

  override refreshSummary(startDate: string, endDate: string) {
    this.loading = true;
    this.reportService.getQMAReport(startDate, endDate, 'quarter').subscribe({
      next: (response) => (this.data = response),
      error: (error: any) => {
        this.loading = false;
        this.data = new ProfitAndLossData();
        this.alertService.error(error, { autoClose: false });
      },
      complete: () => (this.loading = false),
    });
  }
}