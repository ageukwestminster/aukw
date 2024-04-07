import { Component, OnInit } from '@angular/core';
import { NgFor, NgIf } from '@angular/common';
import { Observable, forkJoin } from 'rxjs';
import { concatMap, map, mergeMap, tap, toArray } from 'rxjs/operators';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import {
  AlertService,
  AuthenticationService,
  PayrollService,
  QBEmployeeService,
  QBPayrollService,
  QBRealmService,
} from '@app/_services';
import {
  EmployeeAllocation,
  PayrollJournalEntry,
  IrisPayslip,
  QBRealm,
  User,
} from '@app/_models';
import { SharedModule } from '@app/shared/shared.module';

@Component({
  templateUrl: 'list.component.html',
  standalone: true,
  imports: [NgFor, NgIf, NgbModule, SharedModule],
})
export class PayslipListComponent implements OnInit {
  payslips: IrisPayslip[] = [];
  total: IrisPayslip = new IrisPayslip();
  charityRealm!: QBRealm;
  enterpriseRealm!: QBRealm;
  allocations!: EmployeeAllocation[];
  user!: User;
  employeeJournalEntries$!: Observable<PayrollJournalEntry>;
  currentPayslip: number = 0;
  showProgressBar: boolean = false;
  employeePensionCosts$!: Observable<any>;
  busyOnPensions: boolean = false;
  busyOnEmployerNI: boolean = false;
  busyOnEmployeeJournals: boolean = false;
  busyOnShopJournals: boolean = false;

  constructor(
    private alertService: AlertService,
    private qbRealmService: QBRealmService,
    private authenticationService: AuthenticationService,
    private qbPayrollService: QBPayrollService,
    private payrollService: PayrollService,
    private qbEmployeeService: QBEmployeeService,
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
        tap((allocations) => (this.allocations = allocations)),
      )
      .subscribe({
        error: (error: any) => {
          this.alertService.error('QB Realms not loaded. ' + error, {
            autoClose: false,
          });
        },
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
      this.alertService.error('No payslips found!');
      return;
    }

    this.busyOnEmployerNI = true;

    this.payrollService
      .employerNIAllocatedCosts(this.payslips, this.allocations)
      .pipe(
        toArray(),
        mergeMap((v) =>
          this.qbPayrollService.createEmployerNIJournal(
            this.charityRealm.realmid!,
            v,
            this.payrollDate,
          ),
        ),
      )
      .subscribe({
        next: () => this.alertService.info('Employer NI journal added.'),
        error: (e) => this.alertService.error(e),
        complete: () => (this.busyOnEmployerNI = false),
      });
  }

  makeEmployeeJournalEntries() {
    this.currentPayslip = 0;
    this.showProgressBar = true;
    this.busyOnEmployeeJournals = true;

    this.payrollService
      .employeeJournalEntries(this.payslips, this.allocations)
      .pipe(
        tap(() => this.currentPayslip++), // used to fill progress bar
        mergeMap((v) =>
          this.qbPayrollService.createEmployeeJournal(
            this.charityRealm.realmid!,
            v,
            this.payrollDate,
          ),
        ),
      )
      .subscribe({
        next: () => this.alertService.info('Employee journals added.'),
        error: (e) => this.alertService.error(e),
        complete: () => {
          this.busyOnEmployeeJournals = false;
          this.showProgressBar = false;
        },
      });
  }

  pensionBill() {
    if (!this.payslips || !this.payslips.length) return;

    this.busyOnPensions = true;

    this.payrollService
      .pensionAllocatedCosts(this.payslips, this.allocations)
      .pipe(
        toArray(), // convert to an array, this will form body of post call
        mergeMap((costs) => {
          // Send to api
          return this.qbPayrollService.createPensionBill(
            this.charityRealm.realmid!,
            {
              salarySacrificeTotal: this.total.salarySacrifice.toFixed(2),
              employeePensionTotal: this.total.employeePension.toFixed(2),
              pensionCosts: costs,
              total: (
                this.total.employeePension +
                this.total.salarySacrifice +
                this.total.employerPension
              ).toFixed(2),
            },
            this.payrollDate,
          );
        }),
      )
      .subscribe({
        next: () => this.alertService.info('Pension bill added.'),
        error: (e) => {
          this.busyOnPensions = false;
          this.alertService.error(e);
        },
        complete: () => (this.busyOnPensions = false),
      });
  }

  shopJournals() {
    if (!this.payslips || !this.payslips.length) return;

    this.busyOnShopJournals = true;

    forkJoin({
      payslips: this.payrollService
        .shopPayslips(this.payslips, this.allocations)
        .pipe(toArray()),
      employees: this.qbEmployeeService.getAll(this.enterpriseRealm!.realmid!),
    })
      .pipe(
        map((x) => {
          let returnArray: Array<object> = [];

          x.payslips.forEach((payslip) => {
            const employeeName = x.employees.filter(
              (emp) => emp.payrollNumber == payslip.employeeId,
            )[0];

            returnArray.push({
              quickbooksId: employeeName.quickbooksId,
              totalPay: payslip.totalPay,
              employerNI: payslip.employerNI,
              employerPension: payslip.employerPension,
            });
          });

          return returnArray;
        }),
        mergeMap((data) => {
          return this.qbPayrollService.createShopJournal(
            this.enterpriseRealm!.realmid!,
            data,
            this.payrollDate,
          );
        }),
      )
      .subscribe({
        next: () => this.alertService.info('Shop jopurnals added.'),
        error: (e) => {
          this.busyOnShopJournals = false;
          this.alertService.error(e);
        },
        complete: () => (this.busyOnShopJournals = false),
      });
  }
}
