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
        next: (response: EmployeeAllocation[]) => {
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
    if (!this.payslips || !this.payslips.length) return;

    const employerNIArray: EmployerNIEntry[] = [];

    this.payslips.forEach(payslip => {
      if (payslip.employeeId==45) {
        //console.log(payslip.employeeName);
      }
      const allocations = this.allocations.filter((x) => x.payrollNumber == payslip.employeeId);
      let sum:number= 0;
      if (allocations.length > 1) {
        for (let index = 0; index < allocations.length-1; index++) {
          const alloc = allocations[index];
          const entry = new EmployerNIEntry();
          entry.employeeId = alloc.id;
          entry.class = alloc.class;
          entry.account = alloc.account;
          entry.amount = Number((Math.round(payslip.employerNI*alloc.percentage)/100).toFixed(2));
          sum += entry.amount;
          if (entry.amount) employerNIArray.push(entry);
        }
      } 
      const alloc = allocations[allocations.length-1];
      const entry = new EmployerNIEntry();
      entry.employeeId = alloc.id;
      entry.class = alloc.class;
      entry.account = alloc.account;
      entry.amount = Number((payslip.employerNI - sum).toFixed(2));
      if (entry.amount) employerNIArray.push(entry);

    });


    console.log(JSON.stringify(employerNIArray,null,2));
    return;
    this.qbPayrollService
      .createEmployerNIJournal(employerNIArray, this.charityRealm.realmid!)
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

    this.qbPayrollService
      .createEmployerNIJournal(employerNIArray, this.charityRealm.realmid!)
      .subscribe((x: any) => {
        console.log(x);
      });
  }
}
