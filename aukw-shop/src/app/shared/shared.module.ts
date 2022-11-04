import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

import { AlertComponent } from './alert-component/alert.component';
import { SalesChartComponent } from './sales-chart/sales-chart.component';
import { DepartmentChartComponent } from './dept-chart/dept-chart.component';

@NgModule({
  imports: [CommonModule, FormsModule, ReactiveFormsModule],
  declarations: [AlertComponent, DepartmentChartComponent, SalesChartComponent],
  exports: [
    AlertComponent,
    DepartmentChartComponent,
    SalesChartComponent,
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
  ],
  providers: [],
})
export class SharedModule {}
