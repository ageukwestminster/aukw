import { Routes } from '@angular/router';

import { PayslipListComponent } from './list.component';
import { PayrollComponent } from './payroll.component';

export const PAYROLL_ROUTES: Routes = [
  { path: '', component: PayrollComponent },
];
