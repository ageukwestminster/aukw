import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';

import { IrisPayslip } from '@app/_models';

@Injectable({ providedIn: 'root' })
export class PayslipListService {
  constructor() {}

  private payslipsSubject = new Subject<IrisPayslip[]>();
  payslips$ = this.payslipsSubject.asObservable();

  sendPayslips(payslips: IrisPayslip[]) {
    this.payslipsSubject.next(payslips);
  }
}
