import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { PayrollRoutingModule } from './payroll-routing.module';
import { SharedModule } from '@app/shared/shared.module';

import { PayrollLayoutComponent } from './layout.component';

@NgModule({
  imports: [
    CommonModule,
    ReactiveFormsModule,
    PayrollRoutingModule,
    SharedModule,
    NgbModule,
  ],
  declarations: [
    PayrollLayoutComponent
  ],
})
export class PayrollModule {}
