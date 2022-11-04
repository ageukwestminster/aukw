export class DateRange {
  startDate: string;
  endDate: string;

  constructor(obj?: any) {
    this.startDate = (obj && obj.startDate) || null;
    this.endDate = (obj && obj.endDate) || null;
  }
}
