import { Injectable } from '@angular/core';

import { DateRange, DateRangeEnum } from '@app/_models';

/**
 * Used by the member filter template to set date ranges automatically from a select
 */
@Injectable({ providedIn: 'root' })
export class DateRangeAdapter {

  readonly NOON: number = 12;

  constructor() {}

  customDateRange(start: Date, end: Date): DateRange {
    return this.instantiateObj(start, end);
  }

  /**
   * Given a choice from the DateRange Enum, return the appropiate DAteRange Object. 
   * For example, if today is 9th January 2025 and the Enum 'THIS QUARTER' is selected
   * then it will return an object where endDate is the string 2025-03-31 and the startDate
   * is the striong '2025-01-01'.
   * 
   * Dates are returned in ISO string format.
   * 
   * The trading year is assumed to run from 1-October to 30-September
   * 
   * @param value The value of the Enum to convert into a DateRange object
   * @returns DateRange object containing the start and end date of the range
   */
  enumToDateRange(value: DateRangeEnum): DateRange {
    /** Use 12pm (readonly variable NOON) so that BST -> Z time zone conversion won't affect date
     *  We make this conversion when using getISOString which converts times to Z
     *
     *  This whole method might fail in asian time zones due to using NOON
     * */
    

    var t = new Date();
    var year = t.getFullYear();
    var month = t.getMonth(); // The number of the month: January is 0, February is 1,... December is 11
    var dayOfMonth = t.getDate();
    var dayOfWeek = t.getDay();
    var today = new Date(year, month, dayOfMonth, this.NOON); // Use this.NOON so that BST -> Z time zone conversion won't affect date

    var diffToWeekStart =
      today.getDate() - dayOfWeek + (dayOfWeek == 0 ? -6 : 1);
    var firstDayOfWeek = new Date(new Date(today).setDate(diffToWeekStart));
    var lastDayOfWeek = new Date(
      new Date(firstDayOfWeek).setDate(firstDayOfWeek.getDate() + 7),
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
        var firstDayOfMonth = new Date(year, month, 1, 4, this.NOON);
        var lastDayOfMonth = new Date(year, month + 1, 0, this.NOON);
        return this.instantiateObj(firstDayOfMonth, lastDayOfMonth);
      case DateRangeEnum.THIS_QUARTER:
        var firstDayOfThisQuarter = new Date(year, quarterStartMonth, 1, this.NOON);
        var lastDayOfThisQuarter = new Date(
          year,
          quarterStartMonth + 3,
          0,
          this.NOON,
        );
        return this.instantiateObj(firstDayOfThisQuarter, lastDayOfThisQuarter);
      case DateRangeEnum.THIS_TRADING_YEAR:
        var firstDayOfTradingYear: Date;
        var lastDayOfTradingYear: Date;
        if (month > 8) {
          firstDayOfTradingYear = new Date(year, 9, 1, this.NOON);
          lastDayOfTradingYear = new Date(year + 1, 9, 0, this.NOON);
        } else {
          firstDayOfTradingYear = new Date(year - 1, 9, 1, this.NOON);
          lastDayOfTradingYear = new Date(year, 9, 0, this.NOON);
        }
        return this.instantiateObj(firstDayOfTradingYear, lastDayOfTradingYear);
      case DateRangeEnum.THIS_YEAR:
        var firstDayOfYear = new Date(year, 0, 1, this.NOON);
        var lastDayOfYear = new Date(year + 1, 0, 0, this.NOON);
        return this.instantiateObj(firstDayOfYear, lastDayOfYear);
      case DateRangeEnum.LAST_WEEK:
        var lastDayOfLastWeek = new Date(
          new Date(firstDayOfWeek).setDate(firstDayOfWeek.getDate() - 1),
        );
        var firstDayOfLastWeek = new Date(
          new Date(lastDayOfLastWeek).setDate(lastDayOfLastWeek.getDate() - 7),
        );
        return this.instantiateObj(firstDayOfLastWeek, lastDayOfLastWeek);
      case DateRangeEnum.LAST_MONTH:
        var firstDayOfLastMonth = new Date(year, month - 1, 1, this.NOON);
        var lastDayOfLastMonth = new Date(year, month, 0, this.NOON);
        return this.instantiateObj(firstDayOfLastMonth, lastDayOfLastMonth);
      case DateRangeEnum.LAST_QUARTER:
        var firstDayOfLastQuarter = new Date(
          year,
          quarterStartMonth - 3,
          1,
          this.NOON,
        );
        var lastDayOfLastQuarter = new Date(year, quarterStartMonth, 0, this.NOON);
        return this.instantiateObj(firstDayOfLastQuarter, lastDayOfLastQuarter);
      case DateRangeEnum.LAST_TRADING_YEAR:
        var firstDayOfTradingYear: Date;
        var lastDayOfTradingYear: Date;
        if (month > 8) {
          firstDayOfTradingYear = new Date(year - 1, 9, 1, this.NOON);
          lastDayOfTradingYear = new Date(year, 9, 0, this.NOON);
        } else {
          firstDayOfTradingYear = new Date(year - 2, 9, 1, this.NOON);
          lastDayOfTradingYear = new Date(year - 1, 9, 0, this.NOON);
        }
        return this.instantiateObj(firstDayOfTradingYear, lastDayOfTradingYear);
      case DateRangeEnum.LAST_YEAR:
        var firstDayOfLastYear = new Date(year - 1, 0, 1, this.NOON);
        var lastDayOfLastYear = new Date(year, 0, 0, this.NOON);
        return this.instantiateObj(firstDayOfLastYear, lastDayOfLastYear);
      case DateRangeEnum.LAST_SIX_MONTHS:
        var sixMonthsAgo = new Date(
          month>6?year:year-1, month>6?month-6:12-6+month, dayOfMonth, this.NOON
        );
        return this.instantiateObj(sixMonthsAgo, today);
      case DateRangeEnum.LAST_TWELVE_MONTHS:
          var twelveMonthsAgo = new Date(year-1, month, dayOfMonth, this.NOON);
          return this.instantiateObj(twelveMonthsAgo, today);
      case DateRangeEnum.NEXT_WEEK:
        var firstDayOfNextWeek = new Date(
          new Date(lastDayOfWeek).setDate(lastDayOfWeek.getDate() + 1),
        );
        var lastDayOfNextWeek = new Date(
          new Date(firstDayOfNextWeek).setDate(
            firstDayOfNextWeek.getDate() + 7,
          ),
        );
        return this.instantiateObj(firstDayOfNextWeek, lastDayOfNextWeek);
      case DateRangeEnum.NEXT_MONTH:
        var firstDayOfNextMonth = new Date(year, month + 1, 1, this.NOON);
        var lastDayOfNextMonth = new Date(year, month + 2, 0, this.NOON);
        return this.instantiateObj(firstDayOfNextMonth, lastDayOfNextMonth);
      case DateRangeEnum.NEXT_QUARTER:
        var firstDayOfNextQuarter = new Date(
          year,
          quarterStartMonth + 3,
          1,
          this.NOON,
        );
        var lastDayOfNextQuarter = new Date(
          year,
          quarterStartMonth + 6,
          0,
          this.NOON,
        );
        return this.instantiateObj(firstDayOfNextQuarter, lastDayOfNextQuarter);
      case DateRangeEnum.NEXT_YEAR:
        var firstDayOfNextYear = new Date(year + 1, 0, this.NOON);
        var lastDayOfNextYear = new Date(year + 2, 0, 0, this.NOON);
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
