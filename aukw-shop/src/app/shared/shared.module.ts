import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

import { AlertComponent } from './alert-component/alert.component';

@NgModule({
  imports: [CommonModule, FormsModule, ReactiveFormsModule],
  declarations: [
    AlertComponent,
  ],
  exports: [
    AlertComponent,
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
  ],
  providers: [ ],
})
export class SharedModule {}
