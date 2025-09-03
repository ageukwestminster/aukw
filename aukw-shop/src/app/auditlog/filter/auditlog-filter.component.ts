import { Component, EventEmitter, inject, OnInit, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import {
  FormBuilder,
  FormGroup,
  FormsModule,
  ReactiveFormsModule,
} from '@angular/forms';
import { NgbAccordionModule } from '@ng-bootstrap/ng-bootstrap';
import { Observable, BehaviorSubject } from 'rxjs';

import { DateRange, DateRangeEnum, AuditLogFilter, AuditLog, User } from '@app/_models';
import { AuditLogService, UserService } from '@app/_services';
import { DateRangeChooserComponent } from '@app/shared';
import { DateRangeAdapter } from '@app/_helpers';

@Component({
  selector: 'auditlog-filter',
  templateUrl: './auditlog-filter.component.html',
  standalone: true,
  imports: [
    CommonModule,
    NgbAccordionModule,
    FormsModule,
    ReactiveFormsModule,
    DateRangeChooserComponent
],
})
export class AuditLogFilterComponent implements OnInit {
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
  eventtypes$: Observable<string[]>;

  startAndEndDates!: DateRange;

  private formBuilder = inject(FormBuilder);
  private auditLogService = inject(AuditLogService);
  private userService = inject(UserService);

  constructor() {
    this.users$ = this.userService.getAll();
    this.eventtypes$ = this.auditLogService.getAllEventTypes();
    this.startAndEndDates = (new DateRangeAdapter()).enumToDateRange(DateRangeEnum.THIS_YEAR)
  }

  get f() {
    return this.form.controls;
  }

  ngOnInit(): void {
    this.form = this.formBuilder.group({
      userid: [null],
      eventtype: [null],
    });
  }

  onDateRangeChanged(dateRange: DateRange) {
    this.startAndEndDates = dateRange;
    this.onInputChanged(null);
  }

  onInputChanged(value: string | null) {
    let filter = new AuditLogFilter({
      daterange: this.startAndEndDates,
      userid: this.f['userid'].value,
      eventtype: this.f['eventtype'].value,
    });
    this.refreshSummary(filter);
  }

  refreshSummary(filter: AuditLogFilter) {
    this.working = true;
    this.auditLogService.getFilteredList(filter.toString()).subscribe((response: any) => {
      this.filteredAuditLog.emit(response);
      this.filterSubject.next(filter);
      this.working = false;
    });
  }
}
