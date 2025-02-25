import { Component, inject, OnInit } from '@angular/core';
import { CashRatioMovingAverageChartData } from '@app/_models';

import { AlertService, ReportService } from '@app/_services';
import { CashRatioChartComponent } from '@app/shared/cash-ratio-chart/cash-ratio-chart.component';

/**  report sahowing the ration between cash and credit card recipts */
@Component({
  imports: [CashRatioChartComponent],
  templateUrl: './cash-ratio-report.component.html',
})
export class CashRatioReportComponent implements OnInit {
  loading: boolean = false;
  data!: CashRatioMovingAverageChartData;

  private reportService = inject(ReportService);
  private alertService = inject(AlertService);

  ngOnInit() {
    // Set flag to tell user to expect a wait
    this.loading = true;

    this.reportService.getCashRatioMovingAverage().subscribe({
      next: (result: CashRatioMovingAverageChartData) => {this.data = result;},
      error: (error: any) => {
        this.loading = false;
        this.data = new CashRatioMovingAverageChartData();
        this.alertService.error(error, { autoClose: false });
      },
      complete: () => (this.loading = false),
    });
  }
}
