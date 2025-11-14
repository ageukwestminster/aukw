import { inject } from '@angular/core';
import type {
  ActivatedRouteSnapshot,
  ResolveFn,
  RouterStateSnapshot,
} from '@angular/router';
import { UserService } from '@app/_services';
import { User } from '@app/_models';
import { of, switchMap } from 'rxjs';

export const usersResolver: ResolveFn<User[]> = (
  route: ActivatedRouteSnapshot,
  state: RouterStateSnapshot,
) => {
  const userService = inject(UserService);
  const suspended = route.paramMap.get('suspended') || 'false';
  return userService.getAll().pipe(
    switchMap((users) => {
      if (suspended === 'true') {
        return [users.filter((u) => u.suspended === true)];
      } else {
        return of(users);
      }
    }),
  );
};
