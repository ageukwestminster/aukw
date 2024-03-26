/**
 * Simple class to store an id/string pair
 */
export class ApiMessage {
  id: number;
  message: string;

  constructor(obj?: any) {
    this.id = (obj && obj.id) || 0;
    this.message = (obj && obj.message) || null;
  }
}

export class UploadResponse {
  isEncrypted: boolean;
  message: string;

  constructor(obj?: any) {
    this.isEncrypted = (obj && obj.isEncrypted) || false;
    this.message = (obj && obj.message) || null;
  }
}
