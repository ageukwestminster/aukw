import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { PayrollLayoutComponent } from './layout.component';


const routes: Routes = [
  {
    path: '',
    component: PayrollLayoutComponent,
    children: [
      /*{ path: '', component: UserListComponent },
      { path: 'add', component: UserAddEditComponent },
      { path: 'edit/:id', component: UserAddEditComponent },
      { path: 'suspended/:suspended', component: UserListComponent },*/
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class PayrollRoutingModule {}
