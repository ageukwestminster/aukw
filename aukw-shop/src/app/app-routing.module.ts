import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { CallbackComponent } from './callback';
import { HomeComponent } from './home';
import { LoginComponent } from './login';
import { AuthGuard } from './_helpers';
import { Role } from './_models';

const usersModule = () =>
  import('./users/users.module').then((x) => x.UsersModule);
const takingsModule = () =>
  import('./takings/takings.module').then((x) => x.TakingsModule);
const payrollModule = () =>
  import('./payroll/payroll.module').then((x) => x.PayrollModule);
const reportsModule = () =>
  import('./reports/reports.module').then((x) => x.ReportsModule);

const routes: Routes = [
  {
    path: '',
    component: HomeComponent,
    canActivate: [AuthGuard],
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
    loadChildren: reportsModule,
    canActivate: [AuthGuard],
  },
  {
    path: 'payroll',
    loadChildren: payrollModule,
    canActivate: [AuthGuard],
    data: { roles: [Role.Admin] },
  },
  {
    path: 'users',
    loadChildren: usersModule,
    canActivate: [AuthGuard],
    data: { roles: [Role.Admin] },
  },

  {
    path: 'takings',
    loadChildren: takingsModule,
    canActivate: [AuthGuard],
  },

  // otherwise redirect to home
  { path: '**', redirectTo: '' },
];

/**
 * Routing for the Angular app is configured as an array of Routes, each component
 * is mapped to a path so the Angular Router knows which component to display based
 * on the URL in the browser address bar. The home route is secured by passing the
 * AuthGuard to the canActivate property of the route.
 *
 * The Routes array is passed to the RouterModule.forRoot() method which creates a
 * routing module with all of the app routes configured, and also includes all of
 * the Angular Router providers and directives such as the
 * <router-outlet></router-outlet> directive.
 */
@NgModule({
  imports: [RouterModule.forRoot(routes, { enableTracing: false })],
  exports: [RouterModule],
})
export class AppRoutingModule {}
