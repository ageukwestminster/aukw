import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { TakingsLayoutComponent } from './layout.component';
import { TakingsListComponent } from './list/list.component';
import { TakingsAddEditComponent } from './add-edit/add-edit.component';

const routes: Routes = [
  {
    path: '',
    component: TakingsLayoutComponent,
    children: [
      { path: '', component: TakingsListComponent },
      { path: 'add', component: TakingsAddEditComponent },
      { path: 'edit/:id', component: TakingsAddEditComponent },
      { path: 'view/:id', component: TakingsAddEditComponent },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class TakingsRoutingModule {}
