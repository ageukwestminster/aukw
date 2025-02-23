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
  quantity: number;
  amount: number;
  avgprice: number;
  israg: boolean;

  add(item: SalesByItem): SalesByItem {
    this.quantity += item.quantity;
    this.amount += item.amount;
    this.avgprice = this.quantity ? this.amount / this.quantity : 0;
    return this;
  }

  constructor(obj?: any) {
    this.id = (obj && obj.id) || null;
    this.name = (obj && obj.name) || null;
    this.quantity = (obj && obj.number) || 0;
    this.amount = (obj && obj.amount) || 0;
    this.avgprice = (obj && obj.avgprice) || 0;
    this.israg = (obj && obj.israg) || false;
  }
}
