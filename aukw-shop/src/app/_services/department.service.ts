import { Injectable } from '@angular/core';

import { Department } from '@app/_models';

@Injectable({ providedIn: 'root' })
export class DepartmentService {
  getAll() {
    return Object.values(Department);
  }
}
