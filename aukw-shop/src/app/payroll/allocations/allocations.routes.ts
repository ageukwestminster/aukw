import { Routes } from '@angular/router';

import { AllocationsComponent } from './allocations.component';
import { AllocationsAddEditComponent } from './add-edit/add-edit.component';
import { AddEditAllocationsComponent } from './add-edit-employee-allocations/add-edit-allocations-base.component';

export const ALLOCATIONS_ROUTES: Routes = [
  {
    path: '',
    component: AllocationsComponent,
    children: [
      { path: '', component: AllocationsAddEditComponent },
      { path: 'add', component: AllocationsAddEditComponent },
      { path: 'edit/:id', component: AllocationsAddEditComponent },
    ],
  },
];
