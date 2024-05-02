import { Routes } from '@angular/router';

import { PayrollComponent } from './payroll.component';
import { PayrollLayoutComponent } from './layout.component';
import { PayrollFrontPageComponent } from './frontpage.component';
import { EmployeeAllocationsComponent } from './allocations/employee-allocations.component'

export const PAYROLL_ROUTES: Routes = [
  { 
    path: '', 
    component: PayrollFrontPageComponent,
    children :[
      {
        path: '',
        component: PayrollComponent
      }
    ]
  },
];
