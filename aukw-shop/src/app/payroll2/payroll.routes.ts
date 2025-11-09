import { Routes } from '@angular/router';

import { PayrollComponent } from './payroll.component';
import { PayslipsComponent } from './payslip-list/payslips.component';
import { EmployeeJournalsListComponent } from './transactions/views/employee-journals-list.component';

export const PAYROLL_ROUTES: Routes = [
  {
    path: '',
    component: PayrollComponent,
    children: [
      { path: '', component: PayslipsComponent },
      { path: 'journals', component: EmployeeJournalsListComponent },
      //{ path: 'enterprises', component: ShopJournalComponent },
      /*{ path: 'pensions', component: PensionInvoiceComponent },
      { path: 'employerni', component: EmployerNiComponent },*/
    ],
  },
];
