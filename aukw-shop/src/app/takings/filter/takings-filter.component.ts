import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import { CommonModule, NgIf } from '@angular/common';
import {
  FormGroup,
  FormsModule,
  ReactiveFormsModule,
} from '@angular/forms';
import {
  NgbAccordionModule,
  NgbDatepickerModule,
} from '@ng-bootstrap/ng-bootstrap';
import { environment } from '@environments/environment';
import { Observable, BehaviorSubject } from 'rxjs';

import {
  DateRange,
  DateRangeEnum,
  TakingsFilter,
  TakingsSummary,
} from '@app/_models';
import { TakingsService } from '@app/_services';
import { DateRangeChooserComponent } from '@app/shared';

@Component({
  selector: 'takings-filter',
  templateUrl: './takings-filter.component.html',
  standalone: true,
  imports: [
    CommonModule,
    NgIf,
    NgbDatepickerModule,
    NgbAccordionModule,
    FormsModule,
    ReactiveFormsModule,
    DateRangeChooserComponent,
  ],
})
export class TakingsFilterComponent {
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

  readonly INITIALDATERANGE: DateRangeEnum = DateRangeEnum.LAST_SIX_MONTHS;

  constructor(
    private takingsService: TakingsService,
  ) {}

  get f() {
    return this.form.controls;
  }

  DateRangeEnum = DateRangeEnum;


  // Required so that the template can access the Enum
  // From https://stackoverflow.com/a/59289208
  public get DateRange() {
    return DateRangeEnum;
  }

  onDateRangeObjectChanged(dateRange: DateRange) {
    this.onDateRangeChanged(dateRange.startDate, dateRange.endDate);
  }

  onDateRangeChanged(startDate: string, endDate: string) {
    var str = `start=${startDate!}`;
    str = str.concat('&', 'end=', endDate);

    this.takingsService
      .getSummary(environment.HARROWROAD_SHOPID, str)
      .subscribe((response: any) => {
        this.filteredTakings.emit(response);
      });
  }

}
