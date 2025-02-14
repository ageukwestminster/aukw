import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import { CommonModule, DatePipe, KeyValue, NgFor, NgIf } from '@angular/common';
import {
  FormBuilder,
  FormGroup,
  FormsModule,
  ReactiveFormsModule,
} from '@angular/forms';
import {
  NgbAccordionModule,
  NgbDateAdapter,
  NgbDateParserFormatter,
  NgbDatepickerModule,
  NgbDateStruct,
} from '@ng-bootstrap/ng-bootstrap';
import { environment } from '@environments/environment';
import { Observable, BehaviorSubject } from 'rxjs';

import {
  DateRange,
  DateRangeEnum,
  TakingsFilter,
  TakingsSummary,
} from '@app/_models';
import { DateFormatHelper, TakingsService } from '@app/_services';
import { 
  CustomDateParserFormatter,
  DateRangeAdapter,
  NgbUTCStringAdapter,
} from '@app/_helpers';

@Component({
  selector: 'takings-filter',
  templateUrl: './takings-filter.component.html',
  standalone: true,
  imports: [
    CommonModule,
    NgFor,
    NgIf,
    NgbDatepickerModule,
    NgbAccordionModule,
    FormsModule,
    ReactiveFormsModule,
  ],
  providers: [
    { provide: NgbDateAdapter, useClass: NgbUTCStringAdapter },
    { provide: NgbDateParserFormatter, useClass: CustomDateParserFormatter },
  ],
})
export class TakingsFilterComponent implements OnInit {
  @Output()
  filter: EventEmitter<TakingsFilter> = new EventEmitter<TakingsFilter>();
  @Output() loading: EventEmitter<boolean> = new EventEmitter<boolean>();
  @Output() filteredTakings: EventEmitter<TakingsSummary[]> = new EventEmitter<
    TakingsSummary[]
  >();

  form!: FormGroup;
  filterSubject: BehaviorSubject<TakingsFilter> =
    new BehaviorSubject<TakingsFilter>(new TakingsFilter());
  filter$: Observable<TakingsFilter> = this.filterSubject.asObservable();
  working: boolean = false;
  panelOpen: boolean = false;

  constructor(
    private formBuilder: FormBuilder,
    private dateRangeAdapter: DateRangeAdapter,
    private takingsService: TakingsService,
    private dateFormatHelper: DateFormatHelper,
  ) {}

  get f() {
    return this.form.controls;
  }

  ngOnInit(): void {
    this.form = this.formBuilder.group({
      dateRange: [DateRangeEnum.THIS_YEAR],
      startDate: [null],
      endDate: [null],
    });

    this.onDateRangeChanged(DateRangeEnum.THIS_YEAR);
  }

  // Required so that the template can access the Enum
  // From https://stackoverflow.com/a/59289208
  public get DateRange() {
    return DateRangeEnum;
  }

  /* Used to stop the keyvalues pipe re-arranging the order of the Enum */
  /* From https://stackoverflow.com/a/52794221/6941165 */
  originalOrder = (
    a: KeyValue<string, DateRangeEnum>,
    b: KeyValue<string, DateRangeEnum>,
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

  refreshSummary(startDate: string, endDate: string) {
    var str = `start=${startDate!}`;
    str = str.concat('&', 'end=', endDate);

    this.takingsService
      .getSummary(environment.HARROWROAD_SHOPID, str)
      .subscribe((response: any) => {
        this.filteredTakings.emit(response);
      });
  }

  onRefreshPressed() {
    if (this.f['startDate'].value && this.f['endDate'].value) {
      const start = this.dateFormatHelper.formatedDate(this.f['startDate'].value);
      const end = this.dateFormatHelper.formatedDate(this.f['endDate'].value);
      this.f['dateRange'].setValue(DateRangeEnum.CUSTOM);
      this.refreshSummary(start!, end!);
    }
  }

}
