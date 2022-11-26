import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

import { AlertComponent } from './alert-component/alert.component';
import { SalesChartComponent } from './sales-chart/sales-chart.component';
import { DepartmentChartComponent } from './dept-chart/dept-chart.component';
import { MoneyInputComponent } from './money-input/money-input.component';
import { MonthlySalesChartComponent } from './monthly-sales-chart/monthly-sales-chart.component';
import { SalesHistogramComponent } from './sales-histogram/sales-histogram.component';

@NgModule({
  imports: [CommonModule, FormsModule, ReactiveFormsModule],
  declarations: [
    AlertComponent,
    DepartmentChartComponent,
    MoneyInputComponent,
    SalesChartComponent,
    MonthlySalesChartComponent,
    SalesHistogramComponent,
  ],
  exports: [
    AlertComponent,
    DepartmentChartComponent,
    MoneyInputComponent,
    MonthlySalesChartComponent,
    SalesChartComponent,
    SalesHistogramComponent,
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
  ],
  providers: [],
})
export class SharedModule {}
