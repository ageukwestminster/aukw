import { Routes } from '@angular/router';

import { UserListComponent } from './list.component';
import { UserAddEditComponent } from './add-edit.component';

export const USERS_ROUTES: Routes = [
  { path: '', component: UserListComponent },
  { path: 'add', component: UserAddEditComponent },
  { path: 'edit/:id', component: UserAddEditComponent },
  { path: 'suspended/:suspended', component: UserListComponent },
];
