import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { UsersRoutingModule } from './users-routing.module';
import { SharedModule } from '@app/shared/shared.module';

import { UserLayoutComponent } from './layout.component';
import { UserListComponent } from './list.component';
import { UserAddEditComponent } from './add-edit.component';
import { UserRowComponent } from './row.component';

@NgModule({
  imports: [
    CommonModule,
    ReactiveFormsModule,
    UsersRoutingModule,
    SharedModule,
    NgbModule,
  ],
  declarations: [
    UserLayoutComponent,
    UserListComponent,
    UserAddEditComponent,
    UserRowComponent,
  ],
})
export class UsersModule {}
