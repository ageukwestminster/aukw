import { Component, inject, Input, OnInit } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { KeyValue } from '@angular/common';
import { Router } from '@angular/router';

import { DateRangeAdapter } from '@app/_helpers';
import { DateRange, DateRangeEnum, User } from '@app/_models';
import {
  AuthenticationService,
  DateFormatHelper,
  ExportToCsvService,
  AlertService,
} from '@app/_services';

@Component({
  template: '',
  standalone: true,
  imports: [],
})
export abstract class AbstractChartReportComponent<T = any> implements OnInit {
  protected user: User;
  protected form!: FormGroup;
  protected loading: boolean = false;
  protected enterprises: boolean = true; // When 'true' use Enterprises company, Charity otherwise

  /* The main data that will be displayed in the chart or table */
  protected data!: T;
  /** The first day of the date range that data for which will be retrieved.*/
  protected startDate!: string;
  /** The last day of the date range that data for which will be retrieved.*/
  protected endDate!: string;

  protected exportToCsvService = inject(ExportToCsvService);
  protected dateRangeAdapter = inject(DateRangeAdapter);
  protected formBuilder = inject(FormBuilder);
  protected router = inject(Router);
  protected alertService = inject(AlertService);
  protected dateFormatHelper = inject(DateFormatHelper);
  private authenticationService = inject(AuthenticationService);

  /** Expose the Math object to templates */
  Math = Math;
  /** Expose the DateRangeEnum enum to templates */
  DateRangeEnum = DateRangeEnum;

  constructor() {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit(): void {
    let initialDateRange = DateRangeEnum.THIS_YEAR;
    let dtRng = this.dateRangeAdapter.enumToDateRange(initialDateRange);
    this.form = this.formBuilder.group({
      dateRange: [DateRangeEnum.THIS_YEAR],
      startDate: [dtRng.startDate],
      endDate: [dtRng.endDate],
    });

    this.onDateRangeEnumSelected(initialDateRange);
  }

  /** Convenience getter for easy access to form fields */
  get f() {
    return this.form.controls;
  }

  /** Required so that the template can access the Enum.
   * {@link  https://stackoverflow.com/a/59289208}
   */
  readonly DateRange = DateRangeEnum;

  /** Used to stop the keyvalues pipe re-arranging the order of the Enum.
   * Source: https://stackoverflow.com/a/52794221/6941165 */
  originalOrder = (
    a: KeyValue<string, DateRangeEnum>,
    b: KeyValue<string, DateRangeEnum>,
  ): number => {
    return 0;
  };

  /**
   * Called when the DateRange changes. A DateRange is a startdate/enddate pair.
   * @param dateRange The new DateRange
   */
  onDateRangeChanged(dateRange: DateRange) {
    this.startDate = dateRange.startDate;
    this.endDate = dateRange.endDate;
    this.refreshSummary(dateRange.startDate, dateRange.endDate);
  }

  /** Called when the DateRangeEnum has changed
   * @param value
   */
  onDateRangeEnumSelected(value: string | null) {
    let dateRange: DateRange;
    if (value == null || value == 'null') {
      dateRange = this.dateRangeAdapter.enumToDateRange(
        DateRangeEnum.THIS_YEAR,
      );
      dateRange.startDate = '2000-01-01';
      this.f['startDate'].disable();
      this.f['endDate'].disable();
    } else if (value == DateRangeEnum.CUSTOM) {
      this.f['startDate'].enable();
      this.f['endDate'].enable();
      dateRange = new DateRange({
        startDate: this.f['startDate'].value,
        endDate: this.f['endDate'].value,
      });
    } else {
      this.f['startDate'].enable();
      this.f['endDate'].enable();
      dateRange = this.dateRangeAdapter.enumToDateRange(
        value! as DateRangeEnum,
      );
      this.f['startDate'].setValue(dateRange.startDate);
      this.f['endDate'].setValue(dateRange.endDate);
      this.startDate = dateRange.startDate;
      this.endDate = dateRange.endDate;
    }

    this.refreshSummary(dateRange.startDate, dateRange.endDate);
  }

  /**Implement this function in each child class to bring in the data */
  abstract refreshSummary(startDate: string, endDate: string): void;

  /**
   * Export the data array to a CSV file
   */
  exportToCSV(): void {
    this.exportToCsvService.exportToCSV(this.data);
  }

  /**
   * This checkbox determines if the report is run on the
   * Enterprises company or the Charity company.
   */
  checkboxClick() {
    this.enterprises = !this.enterprises;
    this.onDateRangeEnumSelected(this.f['dateRange'].value);
  }
}
