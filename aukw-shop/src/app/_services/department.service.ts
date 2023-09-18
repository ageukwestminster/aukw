import { Injectable } from '@angular/core';

import { Department } from '@app/_models';

/**
 * This class has a single method which returns a array of 
 * the names of all the available departments.
 * 
 * Departments are defined as an enum, rather than in the database.
 * See {@link ../_models/Department}
 */
@Injectable({ providedIn: 'root' })
export class DepartmentService {
  /**
   * Get a list of the names of all available departments
   * @returns String array of department names
   */
  getAll() {
    return Object.values(Department);
  }
}
