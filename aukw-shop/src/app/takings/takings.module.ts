import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { TakingsRoutingModule } from './takings-routing.module';
import { SharedModule } from '@app/shared/shared.module';

import { TakingsLayoutComponent } from './layout.component';
import { TakingsListComponent } from './list.component';
import { TakingsAddEditComponent } from './add-edit.component';
import { TakingsRowComponent } from './row.component';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        TakingsRoutingModule,
        SharedModule,
        NgbModule
    ],
    declarations: [
        TakingsLayoutComponent,
        TakingsListComponent,
        TakingsAddEditComponent,
        TakingsRowComponent
    ]
})
export class TakingsModule { }