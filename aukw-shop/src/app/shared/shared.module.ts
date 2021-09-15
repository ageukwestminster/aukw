import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

import { AlertComponent } from './alert-component/alert.component';
import { ToastContainerComponent } from './toast-container/toast-container.component';

@NgModule({
  imports: [CommonModule, FormsModule, ReactiveFormsModule],
  declarations: [
    AlertComponent,
    ToastContainerComponent,
  ],
  exports: [
    AlertComponent,
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    ToastContainerComponent,
  ],
  providers: [ ],
})
export class SharedModule {}
