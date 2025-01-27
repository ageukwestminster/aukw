import { Routes } from '@angular/router';

import { ReportsComponent } from './reports.component';
import { SalesListComponent } from './sales-list';
import { SalesHistogramComponent } from './sales-histogram';
import { AukwIntercoComponent } from './aukw-interco';
import { DailyTransactionSizeComponent } from './daily-transaction-size';
import { WeeklySalesComponent } from './weekly-sales';

export const REPORTS_ROUTES: Routes = [
  { path: '', component: ReportsComponent },
  { path: 'sales-list', component: SalesListComponent },
  { path: 'sales-histogram', component: SalesHistogramComponent },
  { path: 'weekly-sales', component: WeeklySalesComponent },
  { path: 'daily-transaction-averages', component: DailyTransactionSizeComponent },
  { path: 'aukw-interco', component: AukwIntercoComponent },
];
