/**
 * Holds the uri that user must visit to authorise a connection
 * between the QuickBooks company file and this app.
 */
export class QBAuthUri {
  /** Visit this uri to authorise the QB connection */
  authUri: string | null;
  /** Instructions to use the uri */
  message: string | null;
  /** A wiki link with further information. */
  further_information: string | null;

  constructor(obj?: any) {
    this.message = (obj && obj.message) || null;
    this.authUri = (obj && obj.authUri) || null;
    this.further_information = (obj && obj.further_information) || null;
  }
}
