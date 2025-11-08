import { Routes } from '@angular/router';

import { PayrollComponent } from './payroll.component';
import { PayslipsComponent } from './payslip-list/payslips.component';
import { ShopJournalComponent } from './transactions/shop-journal/shop-journal.component';
import { EmployeeJournalsComponent } from './transactions/employee-journals/employee-journals.component';
import { PensionInvoiceComponent } from './transactions/employerni-and-pension-invoice/pension-invoice.component';
import { EmployerNiComponent } from './transactions/employerni-and-pension-invoice/employer-ni.component';

import { ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { QBPayrollService } from '@app/_services';
import { inject } from '@angular/core';

export const PAYROLL_ROUTES: Routes = [
  {
    path: '',
    component: PayrollComponent,
    children: [
      { path: '', component: PayslipsComponent },
      { path: 'enterprises', component: ShopJournalComponent },
      { path: 'journals', component: EmployeeJournalsComponent },
      { path: 'pensions', component: PensionInvoiceComponent },
      { path: 'employerni', component: EmployerNiComponent },
    ],
  },
];
