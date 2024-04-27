import { Component } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { Router } from '@angular/router';

import { tap } from 'rxjs/operators';

import { DateRangeAdapter } from '@app/_helpers';
import { SalesChartData } from '@app/_models';
import { SummaryService } from '@app/_services';
import { AbstractChartReportComponent } from '../chart-report.component';

@Component({
  selector: 'aukw-interco',
  templateUrl: './aukw-interco.component.html',
})
export class AukwIntercoComponent extends AbstractChartReportComponent<SalesChartData> {
  form!: FormGroup;
  constructor(
    private summaryService: SummaryService,
    private dateRangeAdapter1: DateRangeAdapter,
    private formBuilder1: FormBuilder,
    private router1: Router,
  ) {
    super(dateRangeAdapter1, formBuilder1, router1);
  }

  refreshSummary() {
    this.summaryService
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
}
