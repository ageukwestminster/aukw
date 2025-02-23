import { SalesByItem } from "./sales-by-item";
export class RaggingQuarter {
  title: string;
  start: string;
  end: string;
  books: SalesByItem;
  clothing: SalesByItem;
  household: SalesByItem;
  rummage: SalesByItem;
  shoes: SalesByItem;
  other: SalesByItem;

  constructor(obj?: any) {
    this.title = (obj && obj.title) || null;
    this.start = (obj && obj.start) || null;
    this.end = (obj && obj.end) || null;

    this.books = (obj && obj.books) || new SalesByItem;
    this.clothing = (obj && obj.clothing) || new SalesByItem;
    this.household = (obj && obj.household) || new SalesByItem;
    this.rummage = (obj && obj.rummage) || new SalesByItem;
    this.shoes = (obj && obj.shoes) || new SalesByItem;
    this.other = (obj && obj.other) || new SalesByItem;
  }

  add(item: RaggingQuarter): RaggingQuarter {
    this.books.add(item.books)
    this.clothing.add(item.clothing);
    this.household.add(item.household);
    this.rummage.add(item.rummage);
    this.shoes.add(item.shoes);
    this.other.add(item.other);
    return this;
  }

}


