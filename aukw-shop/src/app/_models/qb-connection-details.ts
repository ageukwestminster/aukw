/**
 * Defines the properties of a validly configured and connected connection to the QBO system.
 */
export class QBConnectionDetails {
  accesstoken: string;
  accesstokenexpiry: string;
  refreshtoken: string;
  refreshtokenexpiry: string;
  realmid: string;
  companyname: string;
  isRevoking: boolean = false;
  isRefreshing: boolean = false;
  linkcreatoruserid: number;
  linkcreatorname: string  | null;
  linkcreatoremail: string  | null;

  constructor(obj?: any) {
    this.refreshtokenexpiry = (obj && obj.refreshtokenexpiry) || null;
    this.accesstoken = (obj && obj.accesstoken) || null;
    this.accesstokenexpiry = (obj && obj.accesstokenexpiry) || null;
    this.refreshtoken = (obj && obj.refreshtoken) || null;
    this.realmid = (obj && obj.realmid) || null;
    this.companyname = (obj && obj.companyname) || null;
    this.linkcreatorname = (obj && obj.linkcreator) || null;
    this.linkcreatoremail = (obj && obj.linkcreatoremail) || null;
    this.linkcreatoruserid = (obj && obj.userid) || null;
  }
}
