import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { PayrollFileUploadComponent } from './payroll-file-upload/payroll-file-upload.component';
import { MoneyInputComponent } from './money-input/money-input.component';
import { QBConnectionListComponent } from './qbconn-list/list.component';
import { QBConnectionRowComponent } from './qbconn-list/row.component';
import { PasswordInputModalComponent } from './payroll-file-upload/password-input.component';

@NgModule({
  imports: [CommonModule, FormsModule, ReactiveFormsModule, NgbModule],
  declarations: [
    PayrollFileUploadComponent,
    MoneyInputComponent,
    PasswordInputModalComponent,
    QBConnectionListComponent,
    QBConnectionRowComponent,
  ],
  exports: [
    PayrollFileUploadComponent,
    MoneyInputComponent,
    PasswordInputModalComponent,
    QBConnectionListComponent,
    QBConnectionRowComponent,
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
  ],
  providers: [],
})
export class SharedModule {}
