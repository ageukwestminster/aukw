import { Routes } from '@angular/router';

import { ReportsComponent } from './reports.component';
import { SalesListComponent } from './sales-list';
import { SalesHistogramComponent } from './sales-histogram';
import { AukwIntercoComponent } from './aukw-interco';
import { DailyTransactionSizeComponent } from './daily-transaction-size';
import { WeeklySalesComponent } from './weekly-sales';
import { QmaReportComponent } from './qma-report/qma-report.component';
import { PnlReportComponent } from './pnl-report/pnl-report.component';
import { SalesByDepartmentComponent } from './qma-sales-by-dept/sales-by-department.component';

export const REPORTS_ROUTES: Routes = [
  { path: '', component: ReportsComponent },
  { path: 'sales-list', component: SalesListComponent },
  { path: 'sales-histogram', component: SalesHistogramComponent },
  {
    path: 'daily-transaction-averages',
    component: DailyTransactionSizeComponent,
  },
  { path: 'aukw-interco', component: AukwIntercoComponent },
  { path: 'pnl-report', component: PnlReportComponent },

  //QMA reports
  { path: 'qma-report', component: QmaReportComponent },
  { path: 'weekly-sales', component: WeeklySalesComponent },
  { path: 'sales-by-department', component: SalesByDepartmentComponent },
  { path: 'daily-transaction-averages', component: WeeklySalesComponent },
  { path: 'ragging-report', component: WeeklySalesComponent },
];
