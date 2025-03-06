import { Role } from '@app/_models';

/**
 * Define the properties of a user.
 */
export class User {
  id: number;
  shopid: number;
  username: string;
  role: Role;
  suspended: boolean;
  firstname: string;
  surname: string;
  password?: string;
  accessToken?: string;
  isDeleting: boolean = false;
  isUpdating: boolean = false;
  isAdmin: boolean = false;

  constructor(obj?: any) {
    this.id = (obj && obj.id) || null;
    this.shopid = (obj && obj.shopid) || null;
    this.username = (obj && obj.username) || null;
    this.role = (obj && obj.role) || null;
    this.suspended = obj && obj.suspended;
    this.firstname = (obj && obj.firstname) || null;
    this.surname = (obj && obj.surname) || null;
    this.password = (obj && obj.password) || null;
    this.accessToken = (obj && obj.accessToken) || null;
    this.isAdmin = obj && obj.role && obj.role === Role.Admin;
  }
}
