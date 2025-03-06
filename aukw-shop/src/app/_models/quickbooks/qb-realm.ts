import { QBConnectionDetails } from '@app/_models';

/**
 * A realm is another name for an individual QuickBooks Online company file.
 */
export class QBRealm {
  /** The realm ID is a unique ID value which identifies a specific QuickBooks Online company. */
  realmid: string | null;
  /** The name of the specific QuickBooks Online company. */
  name: string | null;
  /** 'True' if the specific QuickBooks Online company is in the QBO sandbox. */
  issandbox: boolean = false;

  /** Details of a connection between the realm and this app. */
  connection: QBConnectionDetails | null;

  constructor(obj?: any) {
    this.realmid = (obj && obj.realmid) || null;
    this.name = (obj && obj.name) || null;
    this.issandbox = (obj && obj.issandbox) || false;
    this.connection = (obj && obj.connection) || null;
  }
}
