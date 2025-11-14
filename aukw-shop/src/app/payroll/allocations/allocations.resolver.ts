import { inject } from '@angular/core';
import type {
  ActivatedRouteSnapshot,
  ResolveFn,
  RouterStateSnapshot,
} from '@angular/router';
import { environment } from '@environments/environment';
import { QBClassService, QBEmployeeService } from '@app/_services';
import { EmployeeName, QBClass } from '@app/_models';

export const employeesResolver: ResolveFn<EmployeeName[]> = (
  route: ActivatedRouteSnapshot,
  state: RouterStateSnapshot,
) => {
  const qbEmployeeService = inject(QBEmployeeService);
  //const userId = route.paramMap.get('id')!;
  return qbEmployeeService.getAll(environment.qboCharityRealmID);
};

export const classesResolver: ResolveFn<QBClass[]> = (
  route: ActivatedRouteSnapshot,
  state: RouterStateSnapshot,
) => {
  const qbClassService = inject(QBClassService);
  return qbClassService.getAllocatableClasses(environment.qboCharityRealmID);
};
