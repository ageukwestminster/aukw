/**
 *  Formatter class that takes the internal representation of the selected date
 *  in a NgbDatePicker and displays it in the format "dd-MMM-yyyy"
 *
 *  Note: There is a separate Adapter class (NgbUTCStringAdapter) to convert stardard
 *  date format (aka NgbDateStruct) to/from MySQL 'YYYY-MM-DD'.
 *
 *  Example at https://ng-bootstrap.github.io/#/components/datepicker/examples#adapter
 *  Further information at https://ng-bootstrap.github.io/#/components/datepicker/overview#date-model
 */
import { Injectable, inject } from '@angular/core';
import {
  NgbDateParserFormatter,
  NgbDateStruct,
} from '@ng-bootstrap/ng-bootstrap';
import { DateFormatHelper } from '@app/_services';

/**
 * This Service handles how the date is rendered and parsed from keyboard i.e. in the bound input field.
 */
@Injectable()
export class CustomDateParserFormatter extends NgbDateParserFormatter {
  private dateFormatHelper = inject(DateFormatHelper);

  parse(value: string): NgbDateStruct | null {
    return this.dateFormatHelper.parseNgbDate(value);
  }

  format(date: NgbDateStruct | null): string {
    return this.dateFormatHelper.formatedDate(date);
  }
}
