import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { ReportsLayoutComponent } from './layout.component';
import { ReportsComponent } from './reports.component';
import { SalesListComponent } from './sales-list';
import { SalesHistogramComponent } from './sales-histogram';
import { AukwIntercoComponent } from './aukw-interco';

const routes: Routes = [
  {
    path: '',
    component: ReportsLayoutComponent,
    children: [
      { path: '', component: ReportsComponent },
      { path: 'sales-list', component: SalesListComponent },
      { path: 'sales-histogram', component: SalesHistogramComponent },
      { path: 'aukw-interco', component: AukwIntercoComponent },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ReportsRoutingModule {}
