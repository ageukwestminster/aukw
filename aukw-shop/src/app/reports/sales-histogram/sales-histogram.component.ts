import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { KeyValue } from '@angular/common';

import { environment } from '@environments/environment';

import { DateRangeAdapter } from '@app/_helpers';
import { DateRange, DateRangeEnum, HistogramChartData } from '@app/_models';
import { ReportService } from '@app/_services';

@Component({
  selector: 'app-sales-histogram',
  templateUrl: './sales-histogram.component.html',
})
export class SalesHistogramComponent implements OnInit {
  histogramChartData?: HistogramChartData;
  form!: FormGroup;
  constructor(
    private reportService: ReportService,
    private dateRangeAdapter: DateRangeAdapter,
    private formBuilder: FormBuilder
  ) {}

  ngOnInit(): void {
    this.form = this.formBuilder.group({
      dateRange: [DateRangeEnum.THIS_YEAR],
      startDate: [null],
      endDate: [null],
    });

    this.onDateRangeChanged(DateRangeEnum.THIS_YEAR);
  }

  /** Convenience getter for easy access to form fields */
  get f() {
    return this.form.controls;
  }

  /** Required so that the template can access the Enum.
  * Source: https://stackoverflow.com/a/59289208 */
  readonly DateRange = DateRangeEnum;

  /** Used to stop the keyvalues pipe re-arranging the order of the Enum. 
   * Source: https://stackoverflow.com/a/52794221/6941165 */
  originalOrder = (
    a: KeyValue<string, DateRangeEnum>,
    b: KeyValue<string, DateRangeEnum>
  ): number => {
    return 0;
  };

  onDateRangeChanged(value: string | null) {
    let dtRng: DateRange;
    if (value == null || value == 'null') {
      dtRng = this.dateRangeAdapter.enumToDateRange(DateRangeEnum.NEXT_YEAR);
      dtRng.startDate = '2000-01-01';
      this.f['startDate'].disable();
      this.f['endDate'].disable();
    } else if (value == DateRangeEnum.CUSTOM) {
      this.f['startDate'].enable();
      this.f['endDate'].enable();
      dtRng = new DateRange({
        startDate: this.f['startDate'].value,
        endDate: this.f['endDate'].value,
      });
    } else {
      this.f['startDate'].enable();
      this.f['endDate'].enable();
      dtRng = this.dateRangeAdapter.enumToDateRange(value! as DateRangeEnum);
      this.f['startDate'].setValue(dtRng.startDate);
      this.f['endDate'].setValue(dtRng.endDate);
    }

    this.refreshSummary(dtRng.startDate, dtRng.endDate);
  }

  onShopChanged(value: string) {
    this.refreshSummary(this.f['startDate'].value, this.f['endDate'].value);
  }

  refreshSummary(startDate: string, endDate: string) {
    this.reportService
      .getSalesHistogram(startDate, endDate, environment.HARROWROAD_SHOPID)
      .subscribe((result) => {
        this.histogramChartData = result;
      });
  }

  onRowSelected(summaryRow: [string, number]) {
    // this.selectedRow = summaryRow;
    // this.detail = true;
  }
}
