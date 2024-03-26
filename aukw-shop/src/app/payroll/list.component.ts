import { Component, OnInit } from '@angular/core';
import { concatMap } from 'rxjs/operators';

import {
  AuthenticationService,
  QBPayrollService,
  QBRealmService,
} from '@app/_services';
import { EmployeeAllocation, IrisPayslip, QBRealm, User } from '@app/_models';

@Component({ templateUrl: 'list.component.html' })
export class PayslipListComponent implements OnInit {
  payslips: IrisPayslip[] = [];
  total: IrisPayslip = new IrisPayslip();
  charityRealm!: QBRealm;
  enterpriseRealm!: QBRealm;
  allocations!: EmployeeAllocation[];
  user!: User;

  constructor(
    private qbRealmService: QBRealmService,
    private authenticationService: AuthenticationService,
    private qbPayrollService: QBPayrollService,
  ) {
    this.user = this.authenticationService.userValue;
  }

  xlsxWasUploaded(payslips: IrisPayslip[]): void {
    this.payslips = payslips;

    this.total = new IrisPayslip();
    payslips.forEach((element) => {
      this.total = this.total.add(element);
    });
  }

  /**
   * initialize the object by populating the 2 realm properties
   */
  ngOnInit() {
    this.qbRealmService
      .getAll(this.user.id)
      .pipe(
        concatMap((realms: QBRealm[]) => {
          realms.forEach((r: QBRealm) => {
            if (!r.issandbox && r.name) {
              if (/enterprises/i.test(r.name)) {
                this.enterpriseRealm = r;
              } else {
                this.charityRealm = r;
              }
            }
          });
          return this.qbPayrollService.getAllocations(
            this.charityRealm.realmid!,
          );
        }),
      )
      .subscribe((response: EmployeeAllocation[]) => {
        this.allocations = response;
      });
  }

  /**
   * Convenience getter to expose value to template
   */
  get payrollDate(): string {
    return this.payslips && this.payslips[0]
      ? this.payslips[0].payrollDate
      : '';
  }

  updateQBO() {
    if (this.payslips && this.payslips[0]) {
      this.payslips[0].inQBO = !this.payslips[0].inQBO;
    }
  }

  employerNI() {
    if (!this.payslips || !this.payslips.length) return;

    const employerNIArray = this.payslips.map((p: IrisPayslip) => {
      return {
        employeeId: p.employeeId,
        employerNI: p.employerNI,
      };
    });

    this.qbPayrollService
      .createEmployerNIJournal(employerNIArray, this.charityRealm.realmid!)
      .subscribe((x: any) => {
        console.log(x);
      });
  }
}
