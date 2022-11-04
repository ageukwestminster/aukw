export class ApiMessage {
  id: number;
  message: string;

  constructor(obj?: any) {
    this.id = (obj && obj.id) || 0;
    this.message = (obj && obj.message) || null;
  }
}
