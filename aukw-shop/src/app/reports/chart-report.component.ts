import { Component, inject, Input, OnInit } from '@angular/core';
import { DatePipe } from '@angular/common';
import { FormBuilder, FormGroup } from '@angular/forms';
import { KeyValue } from '@angular/common';
import { Router } from '@angular/router';

import { NgbDateStruct } from '@ng-bootstrap/ng-bootstrap';

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

  @Input() data!: T;

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

  onDateRangeChanged(value: string | null) {
    let dtRng: DateRange;
    if (value == null || value == 'null') {
      dtRng = this.dateRangeAdapter.enumToDateRange(DateRangeEnum.THIS_YEAR);
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
      const start = this.dateFormatHelper.formatedDate(
        this.f['startDate'].value,
      );
      const end = this.dateFormatHelper.formatedDate(this.f['endDate'].value);
      this.f['dateRange'].setValue(DateRangeEnum.CUSTOM);
      this.refreshSummary(start!, end!);
    }
  }

  refreshSummary(startDate: string, endDate: string) {}

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
    this.onDateRangeChanged(this.f['dateRange'].value);
  }
}
