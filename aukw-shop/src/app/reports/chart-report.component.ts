import { Component, Input, OnInit } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { KeyValue } from '@angular/common';
import { Router } from '@angular/router';

import { NgbDateStruct } from '@ng-bootstrap/ng-bootstrap';

import { DateRangeAdapter } from '@app/_helpers';
import { DateRange, DateRangeEnum } from '@app/_models';

@Component({ template: '' })
export abstract class AbstractChartReportComponent<T = any> implements OnInit {
  protected form!: FormGroup;

  @Input()
  data!: T;

  constructor(
    private dateRangeAdapter: DateRangeAdapter,
    private formBuilder: FormBuilder,
    private router: Router
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

  onRefreshPressed() {
    if (this.f['startDate'].value && this.f['endDate'].value) {
      const start = this.ngbDateToString(this.f['startDate'].value);
      const end = this.ngbDateToString(this.f['endDate'].value);
      this.f['dateRange'].setValue(DateRangeEnum.CUSTOM);
      this.refreshSummary(start!, end!);
    }
  }

  refreshSummary(startDate: string, endDate: string) {}

  onRowSelected(takingsID: number | null) {
    if (takingsID) {
      this.router.navigate([`takings/edit/${takingsID}`]);
    }
  }

  private ngbDateToString(date: NgbDateStruct | null): string | null {
    return date
      ? date.year.toString() +
          '-' +
          String('00' + date.month).slice(-2) +
          '-' +
          String('00' + date.day).slice(-2)
      : null;
  }
}
