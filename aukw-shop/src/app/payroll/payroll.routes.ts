import { Routes } from '@angular/router';

import { PayslipListComponent } from './list.component';
import { PayrollLayoutComponent } from './layout.component';

export const PAYROLL_ROUTES: Routes = [
  { path: '', component: PayrollLayoutComponent },
];
