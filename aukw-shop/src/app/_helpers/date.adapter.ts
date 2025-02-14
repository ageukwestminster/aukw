import { Injectable } from '@angular/core';
import { NgbDateAdapter, NgbDateStruct } from '@ng-bootstrap/ng-bootstrap';

/**
 * Adapter class for angular powered bootstrap ngbDatepicker to convert stardard
 * date format (aka NgbDateStruct) to/from MySQL 'YYYY-MM-DD'. MySql use the ISO
 * 8601 format.
 *
 * Note: There is a separate CustomDateParserFormatter class that handles how the
 * date is represented on the screen in the input box atop the DatePicker
 *
 * Code is from {@link https://stackoverflow.com/a/47945155/6941165}
 * Further information at {@link https://ng-bootstrap.github.io/#/components/datepicker/overview#date-model}
 * Example at {@link https://ng-bootstrap.github.io/#/components/datepicker/examples#adapter}
 */
@Injectable()
export class NgbUTCStringAdapter extends NgbDateAdapter<string> {

  /**
   * Convert a string of format 'yyyy-MMM-dd' into an NgbDateStruct.
   * @param date 
   * @returns 
   */
  fromModel(date: string | null): NgbDateStruct | null {
    return date &&
      Number(date.substring(0, 4)) &&
      Number(date.substring(5, 7) + 1) &&
      Number(date.substring(8, 10))
      ? {
          year: Number(date.substring(0, 4)),
          month: Number(date.substring(5, 7)),
          day: Number(date.substring(8, 10)),
        }
      : null;
  }

  /**
   * Convert a bootstrap NgbDateStruct to an ISO 8601 ('YYYY-MM-') formatted string.
   * @param date The NgbDateStruct to format as a string
   * @returns A string or null
   */
  toModel(date: NgbDateStruct | null): string | null {
    return date
      ? date.year.toString() +
          '-' +
          String('00' + date.month).slice(-2) +
          '-' +
          String('00' + date.day).slice(-2)
      : null;
  }
}
