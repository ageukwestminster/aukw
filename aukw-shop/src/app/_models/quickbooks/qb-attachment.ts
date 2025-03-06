/**
 * Details of an attachment found in Quickbooks
 */
export class QBAttachment {
  /**
   * The location of the file
   */
  filename: string;
  /**
   * The type of QB attachment
   */
  contentType: string;

  constructor(obj?: any) {
    this.filename = (obj && obj.filename) || null;
    this.contentType = (obj && obj.contentType) || null;
  }
}
