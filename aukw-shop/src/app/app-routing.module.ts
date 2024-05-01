import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { CallbackComponent } from './callback';
import { HomeComponent } from './home';
import { LoginComponent } from './login';
import { authGuard } from './_helpers';
import { Role } from './_models';

const usersRoutes = () =>
  import('./users/users.routes').then((x) => x.USERS_ROUTES);
const payrollRoutes = () =>
  import('./payroll/payroll.routes').then((x) => x.PAYROLL_ROUTES);
const takingsModule = () =>
  import('./takings/takings.routes').then((x) => x.TAKINGS_ROUTES);
const reportsModule = () =>
  import('./reports/reports.routes').then((x) => x.REPORTS_ROUTES);

const routes: Routes = [
  {
    path: '',
    component: HomeComponent,
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
    loadChildren: reportsModule,
    canActivate: [authGuard],
  },
  {
    path: 'payroll',
    loadChildren: payrollRoutes,
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
    loadChildren: takingsModule,
    canActivate: [authGuard],
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
