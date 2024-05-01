import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { NgbModule, NgbDateAdapter } from '@ng-bootstrap/ng-bootstrap';

import { TakingsRoutingModule } from './takings-routing.module';
import { NgbUTCStringAdapter } from '@app/_helpers';

import { TakingsLayoutComponent } from './layout.component';
import { TakingsListComponent } from './list/list.component';
import { TakingsAddEditComponent } from './add-edit/add-edit.component';
import { TakingsRowComponent } from './list/row.component';
import { TakingsFilterComponent } from './filter/takings-filter.component';

@NgModule({
  imports: [
    CommonModule,
    ReactiveFormsModule,
    TakingsRoutingModule,
    NgbModule,
  ],
  declarations: [
    TakingsLayoutComponent,
    TakingsListComponent,
    TakingsAddEditComponent,
    TakingsRowComponent,
    TakingsFilterComponent,
  ],
  providers: [{ provide: NgbDateAdapter, useClass: NgbUTCStringAdapter }],
  // NgbDateAdapter to handle MySQL date format (From https://stackoverflow.com/a/47945155/6941165 )
})
export class TakingsModule {}
