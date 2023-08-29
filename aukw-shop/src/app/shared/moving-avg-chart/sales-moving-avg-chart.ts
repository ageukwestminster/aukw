/* Custom Form Control code taken from https://github.com/xiongemi/angular-form-ngxs/ */
import { Component, Input, OnInit } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';

import { DateRange, DateRangeEnum } from '@app/_models';
import { DateRangeAdapter } from '@app/_helpers';

@Component({
  selector: 'address-form',
  templateUrl: './address-form.component.html',
})
export class AddressFormComponent {
  @Input() daterange?: DateRangeEnum;

  private form!: FormGroup;

  constructor(
    private dateRangeAdapter: DateRangeAdapter,
    private formBuilder: FormBuilder,
  ) {}

  ngOnInit(): void {
    this.form = this.formBuilder.group({
      bank: ['0'],
      dateRange: [DateRangeEnum.THIS_YEAR],
      startDate: [null],
      endDate: [null],
    });
  }

  // convenience getters for easy access to form fields
  get f() {
    return this.form.controls;
  }
}
