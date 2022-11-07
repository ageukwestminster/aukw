/*
 *  Formatter class that takes the internal representation of the selected date
 *  in a NgbDatePicker and displays it in the format "dd-MMM-yyyy"
 *
 *  Note: There is a separate Adapter class (NgbUTCStringAdapter) to convert stardard
 *  date format (aka NgbDateStruct) to/from MySQL 'YYYY-MM-DD'.
 *
 *  Example at https://ng-bootstrap.github.io/#/components/datepicker/examples#adapter
 *  Further information at https://ng-bootstrap.github.io/#/components/datepicker/overview#date-model
 */
import { Injectable } from '@angular/core';
import {
  NgbDateParserFormatter,
  NgbDateStruct,
} from '@ng-bootstrap/ng-bootstrap';

/**
 * This Service handles how the date is rendered and parsed from keyboard i.e. in the bound input field.
 */
@Injectable()
export class CustomDateParserFormatter extends NgbDateParserFormatter {
  readonly DELIMITER = '-';
  readonly MONTHS = [
    'JAN',
    'FEB',
    'MAR',
    'APR',
    'MAY',
    'JUN',
    'JUL',
    'AUG',
    'SEP',
    'OCT',
    'NOV',
    'DEC',
  ];

  parse(value: string): NgbDateStruct | null {
    if (value) {
      const date = value.split(this.DELIMITER);
      if (date.length != 3) {
        return null;
      }
      return {
        day: parseInt(date[0], 10),
        month: this.MONTHS.indexOf(date[1].toUpperCase()) + 1,
        year: parseInt(date[2], 10),
      };
    }
    return null;
  }

  format(date: NgbDateStruct | null): string {
    return date
      ? date.day +
          this.DELIMITER +
          this.MONTHS[date.month - 1] +
          this.DELIMITER +
          date.year
      : '';
  }
}
