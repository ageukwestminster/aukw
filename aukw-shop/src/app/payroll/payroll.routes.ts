import { Routes } from '@angular/router';

import { CharityPayrollComponent } from './charity/charity-payroll.component';
import { PayrollFrontPageComponent } from './frontpage.component';
import { EmployeeAllocationsComponent } from './allocations/employee-allocations.component';
import { UploadPayslipsComponent } from './payslips/upload-payslips.component';
import { ShopJournalComponent } from './shop-journal/shop-journal.component';
import { EmployeeJournalsComponent } from './employee-journals/employee-journals.component';

export const PAYROLL_ROUTES: Routes = [
  {
    path: '',
    component: PayrollFrontPageComponent,
    children: [
      { path: 'allocations', component: EmployeeAllocationsComponent },
      { path: 'payslips', component: UploadPayslipsComponent },
      {
        path: 'charity',
        component: CharityPayrollComponent,
      },
      { path: 'enterprises', component: ShopJournalComponent },
      { path: 'journals', component: EmployeeJournalsComponent },
    ],
  },
];
