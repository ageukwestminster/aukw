import { Routes } from '@angular/router';

import { PayrollFrontPageComponent } from './frontpage.component';
import { EmployeeAllocationsComponent } from './allocations/employee-allocations.component';
import { UploadPayslipsComponent } from './payslips/upload-payslips.component';
import { ShopJournalComponent } from '../payroll2/transactions/shop-journal/shop-journal.component';
//import { EmployeeJournalsComponent } from './employee-journals/employee-journals.component';
//import { PensionInvoiceComponent } from './employerni-and-pension-invoice/pension-invoice.component';
//import { EmployerNiComponent } from './employerni-and-pension-invoice/employer-ni.component';

export const PAYROLL_ROUTES: Routes = [
  {
    path: '',
    component: PayrollFrontPageComponent,
    children: [
      { path: 'allocations', component: EmployeeAllocationsComponent },
      { path: 'payslips', component: UploadPayslipsComponent },
      { path: 'enterprises', component: ShopJournalComponent },
      //{ path: 'journals', component: EmployeeJournalsComponent },
      //{ path: 'pensions', component: PensionInvoiceComponent },
      //{ path: 'employerni', component: EmployerNiComponent },
    ],
  },
];
