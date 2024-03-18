import { DateRange } from './date-range';

export class TakingsFilter {
  daterange?: DateRange;
  sales_min?: number;
  sales_max?: number;

  constructor(obj?: any) {
    this.daterange = (obj && obj.daterange) || null;
    this.sales_min = (obj && obj.sales_min) || null;
    this.sales_max = (obj && obj.sales_max) || null;
  }
  /**
   * overload toString
   * From {@link https://stackoverflow.com/a/35361695/6941165 stackoverflow}
   */
  public toString = (): string => {
    var str = ``;

    if (this.sales_min) {
      if (str.length > 0) str = str.concat('&');
      str = str.concat('sales_min=', this.sales_min.toString());
    }

    if (this.daterange) {
      if (this.daterange.startDate) {
        if (str.length > 0) str = str.concat('&');
        str = str.concat('start=', this.daterange.startDate);
      }
      if (this.daterange.endDate) {
        if (str.length > 0) str = str.concat('&');
        str = str.concat('end=', this.daterange.endDate);
      }
    }

    return str;
  };
}
