import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, DatePipe, NgIf } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import {
  NgbAccordionModule,
  NgbCollapseModule,
  NgbDateAdapter,
  NgbDateParserFormatter,
  NgbDatepickerModule,
} from '@ng-bootstrap/ng-bootstrap';
import { QBReportService } from '@app/_services';
import { AbstractChartReportComponent } from '../chart-report.component';
import { DateRange, DateRangeEnum, ProfitAndLossData } from '@app/_models';
import { CustomDateParserFormatter, NgbUTCStringAdapter } from '@app/_helpers';
import { DateRangeChooserComponent } from '@app/shared';

@Component({
  standalone: true,
  imports: [
    CommonModule,
    DatePipe,
    NgbAccordionModule,
    NgbDatepickerModule,
    NgIf,
    NgbCollapseModule,
    ReactiveFormsModule,
    DateRangeChooserComponent,
  ],
  templateUrl: './pnl-report.component.html',
  styleUrl: './pnl-report.component.css',
  providers: [
    { provide: NgbDateAdapter, useClass: NgbUTCStringAdapter },
    { provide: NgbDateParserFormatter, useClass: CustomDateParserFormatter },
  ],
})
export class PnlReportComponent
  extends AbstractChartReportComponent<ProfitAndLossData>
  implements OnInit
{
  private reportService = inject(QBReportService);

  DateRangeEnum = DateRangeEnum;

  /**
   * When 'true' expand the expenses lines.
   * Logic from: {@link https://ng-bootstrap.github.io/#/components/collapse/examples}
   */
  isExpensesExpanded = false;
  /**
   * When 'true' collapse the other income and other expenses lines.
   */
  isOtherIncomeCollapsed = false;
  /**
   * When 'true' collapse the income lines.
   */
  isIncomeCollapsed = false;

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

    this.reportService.getPandLReport(startDate, endDate, this.enterprises).subscribe({
      next: (response) => {
        this.data = response;
      },
      error: (error: any) => {
        this.loading = false;
        this.data = new ProfitAndLossData();
        this.alertService.error(error, { autoClose: false });
      },
      complete: () => (this.loading = false),
    });
  }
}
