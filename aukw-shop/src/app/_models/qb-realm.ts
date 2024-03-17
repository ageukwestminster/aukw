/**
 * A realm is another name for an individual QuickBooks Online company file.
 */
export class QBRealm {
  /** The realm ID is a unique ID value which identifies a specific QuickBooks Online company. */
  realmid: string | null;
  /** The name of the specific QuickBooks Online company. */
  name: string | null;
  /** 'True' if the specific QuickBooks Online company is in tthe QBO sandbox. */
  issandbox: boolean = false;

  constructor(obj?: any) {
    this.realmid = (obj && obj.realmid) || null;
    this.name = (obj && obj.name) || null;
    this.issandbox = (obj && obj.issandbox) || false;
  }
}
