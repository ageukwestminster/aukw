import { Routes } from '@angular/router';

import { PayrollComponent } from './payroll.component';
import { PayslipListComponent } from './payslips/list.component';
import { PayrollFrontPageComponent } from './frontpage.component';
import { EmployeeAllocationsComponent } from './allocations/employee-allocations.component'

export const PAYROLL_ROUTES: Routes = [
  { 
    path: '', 
    component: PayrollFrontPageComponent,
    children :[
      {
        path: '',
        component: PayrollComponent,
        children: [
          { path: 'allocations', component: EmployeeAllocationsComponent },
          { path: 'payslips', component: PayslipListComponent },
        ]
      }
    ]
  },
];
