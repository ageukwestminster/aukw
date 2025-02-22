import { Injectable } from '@angular/core';
import {
  NgbDateParserFormatter,
  NgbDateStruct,
} from '@ng-bootstrap/ng-bootstrap';

@Injectable({ providedIn: 'root' })
export class DateFormatHelper {
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

  constructor(private ngbDateParserFormatter: NgbDateParserFormatter) {}

  public formatedDate(date: NgbDateStruct | null): string {
    if (typeof date === 'string') {
      return date;
    }
    return date
      ? date.day +
          this.DELIMITER +
          this.MONTHS[date.month - 1] +
          this.DELIMITER +
          date.year
      : '';
  }

  /**
   * Convert a string of format 'dd-MMM-yyyy' into an NgbDateStruct.
   *
   * MMM = one of JAN, FEB, MAR, APR, MAY, JUN, JUL, AUG, SEP, OCT, NOV, DEC.
   *
   * The provided date string must use '-' as a delimiter.
   *
   * @param value The string to parse.
   * @returns A valid NgbDateStruct or null
   */
  public parseNgbDate(value: string): NgbDateStruct | null {
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
}
