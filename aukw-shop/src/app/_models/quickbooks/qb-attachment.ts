/**
 * Details of an attachment found in Quickbooks
 */
export class QBAttachment {
  /**
   * The location of the file
   */
  FileName: string;
  /**
   * The type of QB attachment
   */
  ContentType: string;

  constructor(obj?: any) {
    this.FileName = (obj && obj.FileName) || null;
    this.ContentType = (obj && obj.ContentType) || null;
  }
}
