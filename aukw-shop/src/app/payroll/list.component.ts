import { Component, OnInit } from '@angular/core';
import { concatMap, map } from 'rxjs/operators';
import { from } from 'rxjs';

import {
  AlertService,
  AuthenticationService,
  QBPayrollService,
  QBRealmService,
} from '@app/_services';
import { 
  EmployeeAllocation, 
  EmployerNIEntry, 
  IrisPayslip, 
  QBRealm, 
  User 
} from '@app/_models';

@Component({ templateUrl: 'list.component.html' })
export class PayslipListComponent implements OnInit {
  payslips: IrisPayslip[] = [];
  total: IrisPayslip = new IrisPayslip();
  charityRealm!: QBRealm;
  enterpriseRealm!: QBRealm;
  allocations!: EmployeeAllocation[];
  user!: User;

  constructor(
    private alertService: AlertService,
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
        })
      )
      .subscribe({
        next: (response: any) => {
          this.allocations = response;
        },
        error: (error: any) => {
          this.alertService.error('QB Realms not loaded. ' + error, {
            autoClose: false,
          });
        }              
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
    if (!this.payslips || !this.payslips.length) {
      this.alertService.error("No payslips found!");
      return;
    }

    const employerNIArray: EmployerNIEntry[] = [];

    this.payslips.forEach(payslip => {
      const allocations = this.allocations.filter((x) => x.payrollNumber == payslip.employeeId);
      let sum:number= 0;
      if (allocations.length) {
        for (const [i, v] of allocations.entries()) {
          const entry = new EmployerNIEntry({
            "employeeId": v.id,
            "class": v.class,
            "account": v.account,
            "amount": Number((Math.round(payslip.employerNI*v.percentage)/100).toFixed(2))
          });

          // The sum of the allocated amounts must equal the starting total
          // If there is a discrepanc then adjust the final allocated amount.
          sum += entry.amount;
          if (i == (allocations.length-1) && sum != payslip.employerNI) {
            entry.amount += payslip.employerNI - sum;

            // Round to avoid numbers like 65.4000000000004
            entry.amount = Number(entry.amount.toFixed(2)); 
          }
          if (entry.amount) employerNIArray.push(entry);
        }
      }

    });

    // Create QBO Journal entry via api call
    this.qbPayrollService
      .createEmployerNIJournal(this.charityRealm.realmid!, employerNIArray, 
          this.payrollDate)
      .subscribe((x: any) => {
        console.log(x);
      });
  }

  pensionBill() {
    if (!this.payslips || !this.payslips.length) return;

    const employerNIArray = this.payslips.map((p: IrisPayslip) => {
      return {
        employeeId: p.employeeId,
        employerNI: p.employerNI,
      };
    });

  }
}
