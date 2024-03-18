import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { AlertComponent } from './alert-component/alert.component';
import { SalesChartComponent } from './sales-chart/sales-chart.component';
import { DepartmentChartComponent } from './dept-chart/dept-chart.component';
import { MoneyInputComponent } from './money-input/money-input.component';
import { MonthlySalesChartComponent } from './monthly-sales-chart/monthly-sales-chart.component';
import { QBConnectionListComponent } from './qbconn-list/list.component';
import { QBConnectionRowComponent } from './qbconn-list/row.component';
import { SalesHistogramChartComponent } from './sales-histogram/sales-histogram-chart.component';
import { SummaryTableComponent } from './summary-table/summary-table.component';
import { MovingAverageChartComponent } from './moving-avg-chart/moving-avg-chart.component';

@NgModule({
  imports: [CommonModule, FormsModule, ReactiveFormsModule, NgbModule],
  declarations: [
    AlertComponent,
    DepartmentChartComponent,
    MoneyInputComponent,
    SalesChartComponent,
    MonthlySalesChartComponent,
    MovingAverageChartComponent,
    QBConnectionListComponent,
    QBConnectionRowComponent,
    SalesHistogramChartComponent,
    SummaryTableComponent,
  ],
  exports: [
    AlertComponent,
    DepartmentChartComponent,
    MoneyInputComponent,
    MonthlySalesChartComponent,
    MovingAverageChartComponent,
    QBConnectionListComponent,
    QBConnectionRowComponent,
    SalesChartComponent,
    SalesHistogramChartComponent,
    SummaryTableComponent,
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
  ],
  providers: [],
})
export class SharedModule {}
