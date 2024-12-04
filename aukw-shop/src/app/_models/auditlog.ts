/**
 * Define the properties of an entry in the audit log.
 */
export class AuditLog {
  id: number;
  userid: number;
  username: string;
  fullname: string;
  eventtype: string;
  description: string;
  objecttype?: string;
  objectid?: number;

  constructor(obj?: any) {
    this.id = (obj && obj.id) || null;
    this.userid = (obj && obj.userid) || null;
    this.username = (obj && obj.username) || null;
    this.fullname = (obj && obj.fullname) || null;
    this.eventtype = obj && obj.eventtype;
    this.description = (obj && obj.description) || null;
    this.objecttype = (obj && obj.objecttype) || null;
    this.objectid = (obj && obj.objectid) || null;
  }
}
