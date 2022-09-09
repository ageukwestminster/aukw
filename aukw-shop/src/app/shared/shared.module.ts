import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

import { AlertComponent } from './alert-component/alert.component';
import { SalesGraphComponent } from './sales-graph/sales-graph.component';

@NgModule({
  imports: [CommonModule, FormsModule, ReactiveFormsModule],
  declarations: [
    AlertComponent,
    SalesGraphComponent,
  ],
  exports: [
    AlertComponent,
    SalesGraphComponent,
    CommonModule,
    FormsModule,
    ReactiveFormsModule,    
  ],
  providers: [ ],
})
export class SharedModule {}
