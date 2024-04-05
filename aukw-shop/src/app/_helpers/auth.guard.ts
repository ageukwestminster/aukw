import { inject } from '@angular/core';
import {
  Router,
  ActivatedRouteSnapshot,
  RouterStateSnapshot,
} from '@angular/router';

import { AuthenticationService } from '@app/_services';
/**
 * The auth guard is an angular route guard that's used to prevent unauthenticated users
 * from accessing restricted routes.
 *
 * If the method returns true the route is activated (allowed to proceed), otherwise
 * if the method returns false the route is blocked.
 */
export function authGuard(
  route: ActivatedRouteSnapshot,
  state: RouterStateSnapshot,
) {
  const router = inject(Router);
  const authenticationService = inject(AuthenticationService);
  const user = authenticationService.userValue;
  if (user && user.id) {
    // logged in so return true
    return true;
  } else {
    // not logged in so redirect to login page with the return url
    router.navigate(['/login'], {
      queryParams: { returnUrl: state.url },
    });
    return false;
  }
}
