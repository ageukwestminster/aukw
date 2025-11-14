import { Routes } from '@angular/router';

import { UserListComponent } from './list.component';
import { UserAddEditComponent } from './add-edit.component';
import { usersResolver } from './users.resolver';

export const USERS_ROUTES: Routes = [
  { path: '', component: UserListComponent, resolve: {users: usersResolver}},
  { path: 'add', component: UserAddEditComponent },
  { path: 'edit/:id', component: UserAddEditComponent },
  { path: 'suspended/:suspended', component: UserListComponent, resolve: {users: usersResolver} },
];
