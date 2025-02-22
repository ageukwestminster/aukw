/**
 * The form of data returned by the Sales By Item Report
 *
 * Example item:
 *     {
 *       "id": 39,
 *       "name": "Books",
 *       "number": 2857,
 *       "amount": 3705.33,
 *       "avgprice": 1.3,
 *       "israg": false
 *   },
 */
export class SalesByItem {
  id: number;
  name: string;
  number: number;
  amount: number;
  avgprice: number;
  israg: boolean;

  add(item: SalesByItem): SalesByItem {
    this.number += item.number;
    this.amount += item.amount;
    this.avgprice = this.number ? this.amount / this.number : 0;
    return this;
  }

  constructor(obj?: any) {
    this.id = (obj && obj.id) || null;
    this.name = (obj && obj.name) || null;
    this.number = (obj && obj.number) || 0;
    this.amount = (obj && obj.amount) || 0;
    this.avgprice = (obj && obj.avgprice) || 0;
    this.israg = (obj && obj.israg) || false;
  }
}
