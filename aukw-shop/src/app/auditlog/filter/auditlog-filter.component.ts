import { Component, EventEmitter, inject, OnInit, Output } from '@angular/core';
import { CommonModule, NgFor, NgIf } from '@angular/common';
import {
  FormBuilder,
  FormGroup,
  FormsModule,
  ReactiveFormsModule,
} from '@angular/forms';
import {
  NgbAccordionModule,
} from '@ng-bootstrap/ng-bootstrap';
import { Observable, BehaviorSubject } from 'rxjs';

import {
  DateRange,
  AuditLogFilter,
  AuditLog,
  User,
} from '@app/_models';
import { AuditLogService, UserService } from '@app/_services';
import { DateRangeChooserComponent } from '@app/shared'

@Component({
  selector: 'auditlog-filter',
  templateUrl: './auditlog-filter.component.html',
  standalone: true,
  imports: [
    CommonModule,
    NgFor,
    NgIf,
    NgbAccordionModule,
    FormsModule,
    ReactiveFormsModule,
    DateRangeChooserComponent,
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
  startAndEndDates!: DateRange;

  private formBuilder = inject(FormBuilder);
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
      userid: [null],
    });

  }

  onDateRangeChanged(dateRange: DateRange) {
    this.startAndEndDates = dateRange;
    this.refreshSummary(dateRange.startDate, dateRange.endDate);
  }

  onUseridChanged(value: string | null) {
    if (value == null || value.startsWith('0')) {
      this.refreshSummary(this.startAndEndDates.startDate, this.startAndEndDates.endDate);
    } else {
      this.refreshSummary(
        this.startAndEndDates.startDate, 
        this.startAndEndDates.endDate,
        this.f['userid'].value,
      );
    }
  }

  refreshSummary(startDate: string, endDate: string, userid?: string) {
    var str = `start=${startDate!}`;
    str = str.concat('&', 'end=', endDate);

    if (userid) {
      str = str.concat('&', 'userid=', userid);
    }

    this.auditLogService.getFilteredList(str).subscribe((response: any) => {
      this.filteredAuditLog.emit(response);
    });
  }

}
