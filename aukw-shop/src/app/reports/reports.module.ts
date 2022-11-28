import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { ReportsRoutingModule } from './reports-routing.module';
import { SharedModule } from '@app/shared/shared.module';

import { ReportsLayoutComponent } from './layout.component';
import { ReportsComponent } from './reports.component';
import { SalesListComponent } from './sales-list';
import { SalesHistogramComponent } from './sales-histogram';

@NgModule({
  imports: [
    CommonModule,
    ReactiveFormsModule,
    ReportsRoutingModule,
    SharedModule,
    NgbModule,
  ],
  declarations: [
    ReportsComponent,
    ReportsLayoutComponent,
    SalesListComponent,
    SalesHistogramComponent,
  ],
})
export class ReportsModule {}
