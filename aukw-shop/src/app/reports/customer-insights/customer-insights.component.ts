import { Component, inject, OnInit } from '@angular/core';
import { CustomerInsightsChartData } from '@app/_models';
import { RouterLink } from '@angular/router';

import { AlertService, ReportService } from '@app/_services';
import { CustomerInsightsChartComponent } from '@app/shared';

@Component({
  imports: [CustomerInsightsChartComponent, RouterLink],
  templateUrl: './customer-insights.component.html',
  styleUrl: './customer-insights.component.css',
})
export class CustomerInsightsReportComponent implements OnInit {
  loading: boolean = false;
  data!: CustomerInsightsChartData;

  private reportService = inject(ReportService);
  private alertService = inject(AlertService);

  ngOnInit() {
    // Set flag to tell user to expect a wait
    this.loading = true;

    this.reportService.salesByDepartmentAndCustomerMovingAverage().subscribe({
      next: (result: CustomerInsightsChartData) => {
        this.data = result;
      },
      error: (error: any) => {
        this.loading = false;
        this.data = new CustomerInsightsChartData();
        this.alertService.error(error, { autoClose: false });
      },
      complete: () => (this.loading = false),
    });
  }
}
