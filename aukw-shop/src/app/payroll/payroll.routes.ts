import { Routes } from '@angular/router';

import { PayrollComponent } from './payroll.component';
import { PayslipsComponent } from './payslip-list/payslips.component';
import {
  EmployeeJournalsListComponent,
  EnterprisesJournalsListComponent,
  NILinesListComponent,
  PensionLinesListComponent,
} from './transactions/views';

export const PAYROLL_ROUTES: Routes = [
  {
    path: '',
    component: PayrollComponent,
    children: [
      { path: '', component: PayslipsComponent },
      { path: 'journals', component: EmployeeJournalsListComponent },
      { path: 'enterprises', component: EnterprisesJournalsListComponent },
      { path: 'pensions', component: PensionLinesListComponent },
      { path: 'employerni', component: NILinesListComponent },
    ],
  },
];
