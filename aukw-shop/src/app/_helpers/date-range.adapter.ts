import { Injectable } from '@angular/core';

import { DateRange, DateRangeEnum } from '@app/_models';

/**
 * Used by the member filter template to set date ranges automatically from a select
 */
@Injectable({ providedIn: 'root' })
export class DateRangeAdapter {
  constructor() {}

  customDateRange(start: Date, end: Date): DateRange {
    return this.instantiateObj(start, end);
  }

  enumToDateRange(value: DateRangeEnum): DateRange {
    /** Use 12pm (noon) so that BST -> Z time zone conversion won't affect date
     *  We make this conversion when using getISOString which converts times to Z
     *
     *  This whole method might fail in asian time zones due to using NOON
     * */
    const NOON: number = 12;

    var t = new Date();
    var year = t.getFullYear();
    var month = t.getMonth();
    var dayOfMonth = t.getDate();
    var dayOfWeek = t.getDay();
    var today = new Date(year, month, dayOfMonth, NOON); // Use noon so that BST -> Z time zone conversion won't affect date

    var diffToWeekStart =
      today.getDate() - dayOfWeek + (dayOfWeek == 0 ? -6 : 1);
    var firstDayOfWeek = new Date(new Date(today).setDate(diffToWeekStart));
    var lastDayOfWeek = new Date(
      new Date(firstDayOfWeek).setDate(firstDayOfWeek.getDate() + 7)
    );

    /* Get the number of the quarter year (0,3)*/
    var quarter = Math.floor((month + 3) / 3);
    /* Get the number of the start month of the current
      quarter year (0-9) */
    var quarterStartMonth = (quarter - 1) * 3;

    switch (value) {
      case DateRangeEnum.TODAY:
        return this.instantiateObj(today, today);
      case DateRangeEnum.THIS_WEEK:
        return this.instantiateObj(firstDayOfWeek, lastDayOfWeek);
      case DateRangeEnum.THIS_MONTH:
        var firstDayOfMonth = new Date(year, month, 1, 4, NOON);
        var lastDayOfMonth = new Date(year, month + 1, 0, NOON);
        return this.instantiateObj(firstDayOfMonth, lastDayOfMonth);
      case DateRangeEnum.THIS_QUARTER:
        var firstDayOfThisQuarter = new Date(year, quarterStartMonth, 1, NOON);
        var lastDayOfThisQuarter = new Date(
          year,
          quarterStartMonth + 3,
          0,
          NOON
        );
        return this.instantiateObj(firstDayOfThisQuarter, lastDayOfThisQuarter);
      case DateRangeEnum.THIS_TRADING_YEAR:
        var firstDayOfTradingYear: Date;
        var lastDayOfTradingYear: Date;
        if (month > 8) {
          firstDayOfTradingYear = new Date(year, 9, 1, NOON);
          lastDayOfTradingYear = new Date(year + 1, 9, 0, NOON);
        } else {
          firstDayOfTradingYear = new Date(year - 1, 9, 1, NOON);
          lastDayOfTradingYear = new Date(year, 9, 0, NOON);
        }
        return this.instantiateObj(firstDayOfTradingYear, lastDayOfTradingYear);
      case DateRangeEnum.THIS_YEAR:
        var firstDayOfYear = new Date(year, 0, 1, NOON);
        var lastDayOfYear = new Date(year + 1, 0, 0, NOON);
        return this.instantiateObj(firstDayOfYear, lastDayOfYear);
      case DateRangeEnum.LAST_WEEK:
        var lastDayOfLastWeek = new Date(
          new Date(firstDayOfWeek).setDate(firstDayOfWeek.getDate() - 1)
        );
        var firstDayOfLastWeek = new Date(
          new Date(lastDayOfLastWeek).setDate(lastDayOfLastWeek.getDate() - 7)
        );
        return this.instantiateObj(firstDayOfLastWeek, lastDayOfLastWeek);
      case DateRangeEnum.LAST_MONTH:
        var firstDayOfLastMonth = new Date(year, month - 1, 1, NOON);
        var lastDayOfLastMonth = new Date(year, month, 0, NOON);
        return this.instantiateObj(firstDayOfLastMonth, lastDayOfLastMonth);
      case DateRangeEnum.LAST_QUARTER:
        var firstDayOfLastQuarter = new Date(
          year,
          quarterStartMonth - 3,
          1,
          NOON
        );
        var lastDayOfLastQuarter = new Date(year, quarterStartMonth, 0, NOON);
        return this.instantiateObj(firstDayOfLastQuarter, lastDayOfLastQuarter);
      case DateRangeEnum.LAST_TRADING_YEAR:
        var firstDayOfTradingYear: Date;
        var lastDayOfTradingYear: Date;
        if (month > 8) {
          firstDayOfTradingYear = new Date(year - 1, 9, 1, NOON);
          lastDayOfTradingYear = new Date(year, 9, 0, NOON);
        } else {
          firstDayOfTradingYear = new Date(year - 2, 9, 1, NOON);
          lastDayOfTradingYear = new Date(year - 1, 9, 0, NOON);
        }
        return this.instantiateObj(firstDayOfTradingYear, lastDayOfTradingYear);
      case DateRangeEnum.LAST_YEAR:
        var firstDayOfLastYear = new Date(year - 1, 0, 1, NOON);
        var lastDayOfLastYear = new Date(year, 0, 0, NOON);
        return this.instantiateObj(firstDayOfLastYear, lastDayOfLastYear);
      case DateRangeEnum.NEXT_WEEK:
        var firstDayOfNextWeek = new Date(
          new Date(lastDayOfWeek).setDate(lastDayOfWeek.getDate() + 1)
        );
        var lastDayOfNextWeek = new Date(
          new Date(firstDayOfNextWeek).setDate(firstDayOfNextWeek.getDate() + 7)
        );
        return this.instantiateObj(firstDayOfNextWeek, lastDayOfNextWeek);
      case DateRangeEnum.NEXT_MONTH:
        var firstDayOfNextMonth = new Date(year, month + 1, 1, NOON);
        var lastDayOfNextMonth = new Date(year, month + 2, 0, NOON);
        return this.instantiateObj(firstDayOfNextMonth, lastDayOfNextMonth);
      case DateRangeEnum.NEXT_QUARTER:
        var firstDayOfNextQuarter = new Date(
          year,
          quarterStartMonth + 3,
          1,
          NOON
        );
        var lastDayOfNextQuarter = new Date(
          year,
          quarterStartMonth + 6,
          0,
          NOON
        );
        return this.instantiateObj(firstDayOfNextQuarter, lastDayOfNextQuarter);
      case DateRangeEnum.NEXT_YEAR:
        var firstDayOfNextYear = new Date(year + 1, 0, NOON);
        var lastDayOfNextYear = new Date(year + 2, 0, 0, NOON);
        return this.instantiateObj(firstDayOfNextYear, lastDayOfNextYear);
      case DateRangeEnum.CUSTOM:
      default:
        return new DateRange();
    }
  }

  private instantiateObj(startdate: Date, enddate: Date): DateRange {
    return new DateRange({
      startDate: startdate.toISOString().split('T')[0],
      endDate: enddate.toISOString().split('T')[0],
    });
  }
}
