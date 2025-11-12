import { Routes } from '@angular/router';

import { AllocationsComponent } from './allocations.component';
import { AllocationsAddEditComponent } from './add-edit/add-edit.component';

export const ALLOCATIONS_ROUTES: Routes = [
  {
    path: '',
    component: AllocationsComponent,
    children: [
      { path: '', component: AllocationsAddEditComponent },
      { path: 'edit/:id', component: AllocationsAddEditComponent },
    ],
  },
];
