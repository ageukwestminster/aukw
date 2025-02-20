import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';

import { tap } from 'rxjs/operators';

import { environment } from '@environments/environment';

import { AvgWeeklySalesData } from '@app/_models';
import { ReportService } from '@app/_services';
import { AbstractChartReportComponent } from '../chart-report.component';

@Component({
  templateUrl: './weekly-sales.component.html',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterLink],
})
export class WeeklySalesComponent implements OnInit {
  private reportService = inject(ReportService);
  data: AvgWeeklySalesData[] = [];

  ngOnInit() {
    this.refreshSummary();
  }

  refreshSummary() {
    this.reportService
      .getAverageWeeklySalesData(environment.HARROWROAD_SHOPID)
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
}
