import { Routes } from '@angular/router';

import { AuditLogComponent } from './auditlog';
import { CallbackComponent } from './callback';
import { QuickbooksconnectionComponent } from './quickbooksconnection';
import { HomeComponent } from './home';
import { LoginComponent } from './login';
import { authGuard } from './_helpers';
import { Role } from './_models';
import { AukwIntercoComponent } from './reports/aukw-interco';

const usersRoutes = () =>
  import('./users/users.routes').then((x) => x.USERS_ROUTES);
const payrollRoutes = () =>
  import('./payroll/payroll.routes').then((x) => x.PAYROLL_ROUTES);
const takingsRoutes = () =>
  import('./takings/takings.routes').then((x) => x.TAKINGS_ROUTES);
const reportsRoutes = () =>
  import('./reports/reports.routes').then((x) => x.REPORTS_ROUTES);

export const APP_ROUTES: Routes = [
  {
    path: '',
    //component: AukwIntercoComponent,
    component: AuditLogComponent,
    canActivate: [authGuard],
  },
  {
    path: 'login',
    component: LoginComponent,
  },
  {
    path: 'callback',
    component: CallbackComponent,
  },
  {
    path: 'reports',
    loadChildren: reportsRoutes,
    canActivate: [authGuard],
  },
  {
    path: 'payroll',
    loadChildren: payrollRoutes,
    canActivate: [authGuard],
    data: { roles: [Role.Admin] },
  },
  {
    path: 'auditlog',
    component: AuditLogComponent,
    canActivate: [authGuard],
    data: { roles: [Role.Admin] },
  },
  {
    path: 'users',
    loadChildren: usersRoutes,
    canActivate: [authGuard],
    data: { roles: [Role.Admin] },
  },

  {
    path: 'takings',
    loadChildren: takingsRoutes,
    canActivate: [authGuard],
  },

  {
    path: 'quickbooksconnection',
    component: QuickbooksconnectionComponent,
    canActivate: [authGuard],
    data: { roles: [Role.Admin] },
  },

  // otherwise redirect to home
  { path: '**', redirectTo: '' },
];
