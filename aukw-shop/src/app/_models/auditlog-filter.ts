import { DateRange } from '@app/_models';

export class AuditLogFilter {
  daterange?: DateRange;
  userid?: number;
  eventtype?: string;

  constructor(obj?: any) {
    this.daterange = (obj && obj.daterange) || null;
    this.userid = (obj && obj.userid) || null;
    this.eventtype = (obj && obj.eventtype) || null;
  }
  /**
   * overload toString
   * From {@link https://stackoverflow.com/a/35361695/6941165 stackoverflow}
   */
  public toString = (): string => {
    var str = ``;

    if (this.userid) {
      if (str.length > 0) str = str.concat('&');
      str = str.concat('userid=', this.userid.toString());
    }

    if (this.eventtype) {
      if (str.length > 0) str = str.concat('&');
      str = str.concat('eventtype=', this.eventtype);
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
