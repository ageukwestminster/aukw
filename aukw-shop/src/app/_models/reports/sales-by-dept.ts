import { Shop } from '@app/_models';
/**
 * The form of data returned by the Sales By Department Report
 */
export class SalesByDepartment {
  shopid: Shop;
  start: string;
  end: string;
  clothing: number;
  brica: number;
  books: number;
  linens: number;
  other: number;
  total: number;

  constructor(obj?: any) {
    this.shopid = (obj && obj.shopid) || null;
    this.start = (obj && obj.start) || null;
    this.end = (obj && obj.end) || null;
    this.clothing = (obj && obj.clothing) || 0;
    this.brica = (obj && obj.brica) || 0;
    this.books = (obj && obj.books) || 0;
    this.linens = (obj && obj.linens) || 0;
    this.other = (obj && obj.other) || 0;
    this.total = (obj && obj.total) || 0;
  }
}

/*

{
    "start": "2024-10-01",
    "end": "2024-12-31",
    "shopid": 1,
    "clothing": 17116.1,
    "brica": 14088.72,
    "books": 919.9,
    "linens": 1165.4,
    "other": 0,
    "total": 33290.12
}

*/
