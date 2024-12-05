import { Component, EventEmitter, inject, OnInit, Output } from '@angular/core';
import { CommonModule, KeyValue, NgFor, NgIf } from '@angular/common';
import {
  FormBuilder,
  FormGroup,
  FormsModule,
  ReactiveFormsModule,
} from '@angular/forms';
import {
  NgbAccordionModule,
  NgbDatepickerModule,
  NgbDateStruct,
} from '@ng-bootstrap/ng-bootstrap';
import { Observable, BehaviorSubject } from 'rxjs';

import {
  DateRange,
  DateRangeEnum,
  AuditLogFilter,
  AuditLog,
  User,
} from '@app/_models';
import { AuditLogService, UserService } from '@app/_services';
import { DateRangeAdapter } from '@app/_helpers';

@Component({
  selector: 'auditlog-filter',
  templateUrl: './auditlog-filter.component.html',
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
})
export class AuditLogFilterComponent implements OnInit {
  @Output()
  filter: EventEmitter<AuditLogFilter> = new EventEmitter<AuditLogFilter>();
  @Output() loading: EventEmitter<boolean> = new EventEmitter<boolean>();
  @Output() filteredAuditLog: EventEmitter<AuditLog[]> = new EventEmitter<
    AuditLog[]
  >();

  form!: FormGroup;
  filterSubject: BehaviorSubject<AuditLogFilter> =
    new BehaviorSubject<AuditLogFilter>(new AuditLogFilter());
  filter$: Observable<AuditLogFilter> = this.filterSubject.asObservable();
  working: boolean = false;
  panelOpen: boolean = false;
  users$: Observable<User[]>;

  private formBuilder = inject(FormBuilder);
  private dateRangeAdapter = inject(DateRangeAdapter);
  private auditLogService = inject(AuditLogService);
  private userService = inject(UserService);

  constructor() {
    this.users$ = this.userService.getAll();
  }

  get f() {
    return this.form.controls;
  }

  ngOnInit(): void {
    this.form = this.formBuilder.group({
      dateRange: [DateRangeEnum.THIS_YEAR],
      startDate: [null],
      endDate: [null],
      userid: [null],
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

  onUseridChanged(value: string | null) {
    if (value == null || value.startsWith('0')) {
      this.refreshSummary(this.f['startDate'].value, this.f['endDate'].value)
    } else {
      this.refreshSummary(this.f['startDate'].value, this.f['endDate'].value, this.f['userid'].value)
    }
  }

  refreshSummary(startDate: string, endDate: string, userid?: string) {

    var str = `start=${startDate!}`;
    str = str.concat('&', 'end=', endDate);

    if (userid) {
      str = str.concat('&', 'userid=', userid);
    }  

    this.auditLogService
      .getFilteredList(str)
      .subscribe((response: any) => {
        this.filteredAuditLog.emit(response);
      });
  }

  onRefreshPressed() {
    if (this.f['startDate'].value && this.f['endDate'].value) {
      const start = this.ngbDateToString(this.f['startDate'].value);
      const end = this.ngbDateToString(this.f['endDate'].value);
      this.f['dateRange'].setValue(DateRangeEnum.CUSTOM);
      this.refreshSummary(start!, end!);
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
