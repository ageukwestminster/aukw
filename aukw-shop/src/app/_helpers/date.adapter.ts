/*
 *  Adapter class for angular powered bootstrap ngbDatepicker to convert stardard
 *  date format to MySQL 'YYYY-MM-DD'.
 *
 *  Code is from https://stackoverflow.com/a/47945155/6941165
 *  Further information at https://ng-bootstrap.github.io/#/components/datepicker/overview#date-model
 *  Example at https://ng-bootstrap.github.io/#/components/datepicker/examples#adapter
 */

import { Injectable } from '@angular/core';
import { NgbDateAdapter, NgbDateStruct } from '@ng-bootstrap/ng-bootstrap';

@Injectable()
export class NgbUTCStringAdapter extends NgbDateAdapter<string> {
  fromModel(date: string): NgbDateStruct | null {
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

  toModel(date: NgbDateStruct): string | null {
    return date
      ? date.year.toString() +
          '-' +
          String('00' + date.month).slice(-2) +
          '-' +
          String('00' + date.day).slice(-2)
      : null;
  }
}
