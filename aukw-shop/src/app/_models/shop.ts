/**
 * Defines the properties of a shop
 */
export class Shop {
  id: number;
  name: string;

  constructor(obj?: any) {
    this.id = (obj && obj.id) || null;
    this.name = (obj && obj.name) || null;
  }
}
