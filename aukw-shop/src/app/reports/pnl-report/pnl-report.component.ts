import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, NgIf } from '@angular/common';
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
import { DateRangeEnum, ProfitAndLossData } from '@app/_models';
import {
  CustomDateParserFormatter,
  NgbUTCStringAdapter,
} from '@app/_helpers';

@Component({
  standalone: true,
  imports: [
    CommonModule,
    NgbAccordionModule,
    NgbDatepickerModule,
    NgIf,
    NgbCollapseModule,
    ReactiveFormsModule,
  ],
  templateUrl: './pnl-report.component.html',
  styleUrl: './pnl-report.component.css',
  providers: [
    { provide: NgbDateAdapter, useClass: NgbUTCStringAdapter },
    { provide: NgbDateParserFormatter, useClass: CustomDateParserFormatter }
  ],
})
export class PnlReportComponent 
  extends AbstractChartReportComponent<ProfitAndLossData>
  implements OnInit
{
  private reportService = inject(QBReportService);

  /**
   * When 'true' collapse the expenses lines. 
   * Logic from: {@link https://ng-bootstrap.github.io/#/components/collapse/examples}
   */
  isExpensesCollapsed = false;
    /**
   * When 'true' collapse the other income and other expenses lines. 
   * Logic from: {@link https://ng-bootstrap.github.io/#/components/collapse/examples}
   */
  isOtherIncomeCollapsed = false;

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

    let period:string;

    switch (this.form.controls['dateRange'].value as DateRangeEnum) {
      case DateRangeEnum.LAST_YEAR:
      case DateRangeEnum.LAST_TRADING_YEAR:
      case DateRangeEnum.LAST_TWELVE_MONTHS:
        period = 'Year'
        break;
      case DateRangeEnum.LAST_MONTH:
      case DateRangeEnum.NEXT_MONTH:
      case DateRangeEnum.THIS_MONTH:
        period = 'Month'
        break;
      case DateRangeEnum.LAST_QUARTER:
      case DateRangeEnum.THIS_QUARTER:
      case DateRangeEnum.NEXT_QUARTER:
      case DateRangeEnum.CUSTOM:      
      default:
        period = 'Quarter'
        break;
    }

    this.reportService.getQMAReport(startDate, endDate, period).subscribe({
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