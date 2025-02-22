import {
  Component,
  EventEmitter,
  inject,
  Input,
  OnInit,
  Output,
} from '@angular/core';
import { KeyValue, KeyValuePipe, NgIf, NgFor } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import {
  NgbDateAdapter,
  NgbDateParserFormatter,
  NgbDatepickerModule,
} from '@ng-bootstrap/ng-bootstrap';

import { DateRangeAdapter } from '@app/_helpers';
import { DateRange, DateRangeEnum } from '@app/_models';
import { CustomDateParserFormatter, NgbUTCStringAdapter } from '@app/_helpers';
import { DateFormatHelper } from '@app/_services';

@Component({
  selector: 'date-range-chooser',
  standalone: true,
  imports: [KeyValuePipe, NgbDatepickerModule, NgFor, NgIf, ReactiveFormsModule],
  templateUrl: './date-range-chooser.component.html',
  styleUrl: './date-range-chooser.component.css',
  providers: [
    { provide: NgbDateAdapter, useClass: NgbUTCStringAdapter },
    { provide: NgbDateParserFormatter, useClass: CustomDateParserFormatter },
  ],
})
export class DateRangeChooserComponent implements OnInit {
  @Output() dateDangeChosen: EventEmitter<DateRange> =
    new EventEmitter<DateRange>();
  @Input() initialDateRangeEnum: DateRangeEnum = DateRangeEnum.THIS_YEAR;

  form!: FormGroup;

  private dateRangeAdapter = inject(DateRangeAdapter);
  private formBuilder = inject(FormBuilder);
  private dateFormatHelper = inject(DateFormatHelper);

  ngOnInit(): void {
    let dtRng = this.dateRangeAdapter.enumToDateRange(this.initialDateRangeEnum);
    this.form = this.formBuilder.group({
      dateRange: this.initialDateRangeEnum,
      startDate: [dtRng.startDate],
      endDate: [dtRng.endDate],
    });
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

    this.dateDangeChosen.emit(dtRng);
  }

  onRefreshPressed() {
    if (this.f['startDate'].value && this.f['endDate'].value) {

      let dtRng = this.dateRangeAdapter.customDateRangeFromString(this.f['startDate'].value, this.f['endDate'].value);

      this.dateDangeChosen.emit(dtRng);
    }
  }
}
