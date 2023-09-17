/**
 * Defines the properties of a validly configured and connected connection to the QBO system.
 */
export class QBConnectionDetails {
  accesstoken: string;
  accesstokenexpiry: string;
  refreshtoken: string;
  refreshtokenexpiry: string;

  constructor(obj?: any) {
    this.refreshtokenexpiry = (obj && obj.refreshtokenexpiry) || null;
    this.accesstoken = (obj && obj.accesstoken) || null;
    this.accesstokenexpiry = (obj && obj.accesstokenexpiry) || null;
    this.refreshtoken = (obj && obj.refreshtoken) || null;
  }
}
