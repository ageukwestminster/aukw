import { Injectable, inject } from '@angular/core';
import { Resolve } from '@angular/router';

import { Observable, of } from 'rxjs';
import { delay } from 'rxjs/operators';

import { QBPayrollService } from '@app/_services';
import { IrisPayslip } from '@app/_models';

@Injectable({
  providedIn: 'root'
})
export class PayslipsResolver implements Resolve<Observable<IrisPayslip[]>> {
  protected qbPayrollService = inject(QBPayrollService);

  resolve(): Observable<IrisPayslip[]> {
    return this.qbPayrollService.payslips$;
  }
}
