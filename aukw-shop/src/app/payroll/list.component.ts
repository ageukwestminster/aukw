import { Component, OnInit } from '@angular/core';
import { RouterLink } from '@angular/router';
import { NgFor, NgIf } from '@angular/common';
import { Observable, forkJoin } from 'rxjs';
import { concatMap, filter, map, mergeMap, tap, toArray } from 'rxjs/operators';
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
  imports: [NgFor, NgIf, NgbModule, RouterLink, SharedModule],
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

  qboAuthorisationMissing: boolean = false;

  loading: boolean = false;
  busyOnPensions: boolean = false;
  busyOnEmployerNI: boolean = false;
  busyOnEmployeeJournals: boolean = false;
  busyOnShopJournals: boolean = false;
  disablePensions: boolean = false;
  disableEmployerNI: boolean = false;
  disableEmployeeJournals: boolean = false;
  disableShopJournals: boolean = false;

  fileUploadStatus: string = '';

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
    payslips.forEach((payslip) => {
      this.total = this.total.add(payslip);

      if (
        this.allocations.find(
          (item) =>
            item.isShopEmployee && item.payrollNumber == payslip.employeeId,
        )
      ) {
        payslip.isShopEmployee = true;
      }
    });

    this.updateInQBOValues();
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
            if (!r.connection || !r.connection.refreshtoken) {
              this.qboAuthorisationMissing = true;
            }

            if (!r.issandbox && r.name) {
              if (/enterprises/i.test(r.name)) {
                this.enterpriseRealm = r;
              } else {
                this.charityRealm = r;
              }
            }
          });

          if (
            this.charityRealm &&
            this.charityRealm.connection &&
            this.enterpriseRealm &&
            this.enterpriseRealm.connection
          ) {
            return this.qbPayrollService.getAllocations(
              this.charityRealm.realmid!,
            );
          } else {
            // Flag that user needs to authorise this app in QBO
            this.qboAuthorisationMissing = true;
            throw new Error('This app is not authorised in QBO.');
          }
        }),
        tap((allocations) => (this.allocations = allocations)),
      )
      .subscribe({
        error: (error: any) => {
          this.alertService.error(error);
        },
        complete: () => (this.qboAuthorisationMissing = false),
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

  updateInQBOValues() {
    if (
      this.payslips &&
      this.payslips[0] &&
      this.charityRealm.realmid &&
      this.enterpriseRealm.realmid
    ) {
      const dt = new Date(this.payslips[0].payrollDate + 'T12:00:00');
      const month = (dt.getMonth() + 1).toString().padStart(2, '0');
      const year = dt.getFullYear().toString();

      this.loading = true;

      forkJoin({
        charityPayslips: this.qbPayrollService.getWhatsAlreadyInQBO(
          this.charityRealm.realmid,
          year,
          month,
        ),
        shopPayslips: this.qbPayrollService.getWhatsAlreadyInQBO(
          this.enterpriseRealm.realmid!,
          year,
          month,
        ),
      })
        .pipe(
          map((x) => {
            x.charityPayslips.forEach((quickbooksPayslip: IrisPayslip) => {
              const xlsxPayslip = this.payslips.find(
                (item) => item.employeeId == quickbooksPayslip.employeeId,
              );

              if (xlsxPayslip) {
                this.updateInQBOCharityValues(xlsxPayslip, quickbooksPayslip);
              }
            });
            x.shopPayslips.forEach((quickbooksPayslip: IrisPayslip) => {
              const xlsxPayslip = this.payslips.find(
                (item) => item.employeeId == quickbooksPayslip.employeeId,
              );

              if (xlsxPayslip) {
                this.updateInQBOEnterprisesValues(
                  xlsxPayslip,
                  quickbooksPayslip,
                );
              }
            });
          }),
        )
        .subscribe({
          error: (e) => this.alertService.error(e),
          complete: () => {
            this.loading = false;
          },
        });
    }
  }

  employerNI() {
    if (!this.payslips || !this.payslips.length) {
      this.alertService.error('No payslips found!');
      return;
    }

    this.busyOnEmployerNI = true;

    this.payrollService
      .employerNIAllocatedCosts(
        this.payslips.filter((payslip) => !payslip.niJournalInQBO),
        this.allocations,
      )
      .pipe(
        toArray(),
        filter((term) => term && term.length > 0),
        mergeMap((allocatedEmployerNICosts) =>
          this.qbPayrollService.createEmployerNIJournal(
            this.charityRealm.realmid!,
            allocatedEmployerNICosts,
            this.payrollDate,
          ),
        ),
      )
      .subscribe({
        next: () => this.alertService.info('Employer NI journal processed.'),
        error: (e) => this.alertService.error(e),
        complete: () => {
          this.busyOnEmployerNI = false;
          this.disableEmployerNI = true;
        },
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
        next: () =>
          this.alertService.info('Individual employee journals added.'),
        error: (e) => this.alertService.error(e),
        complete: () => {
          this.busyOnEmployeeJournals = false;
          this.showProgressBar = false;
          this.disableEmployeeJournals = true;
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
        next: () => this.alertService.info('L&G Pension bill added.'),
        error: (e) => {
          this.busyOnPensions = false;
          this.alertService.error(e);
        },
        complete: () => {
          this.busyOnPensions = false;
          this.disablePensions = true;
        },
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
        next: () => this.alertService.info(' Shop payroll journal added.'),
        error: (e) => {
          this.busyOnShopJournals = false;
          this.alertService.error(e);
        },
        complete: () => {
          this.busyOnShopJournals = false;
          this.disableShopJournals = true;
        },
      });
  }

  private updateInQBOCharityValues(
    xlsxPayslip: IrisPayslip,
    quickbooksPayslip: IrisPayslip,
  ): void {
    xlsxPayslip.niJournalInQBO =
      xlsxPayslip.employerNI == quickbooksPayslip.employerNI;

    xlsxPayslip.pensionBillInQBO =
      xlsxPayslip.employerPension == quickbooksPayslip.employerPension;

    xlsxPayslip.payslipJournalInQBO =
      xlsxPayslip.totalPay == quickbooksPayslip.totalPay &&
      xlsxPayslip.paye == quickbooksPayslip.paye &&
      xlsxPayslip.employeeNI == quickbooksPayslip.employeeNI &&
      xlsxPayslip.otherDeductions == quickbooksPayslip.otherDeductions &&
      xlsxPayslip.employeePension == quickbooksPayslip.employeePension &&
      xlsxPayslip.salarySacrifice == quickbooksPayslip.salarySacrifice &&
      xlsxPayslip.studentLoan == quickbooksPayslip.studentLoan &&
      xlsxPayslip.netPay == quickbooksPayslip.netPay;
  }

  private updateInQBOEnterprisesValues(
    xlsxPayslip: IrisPayslip,
    quickbooksPayslip: IrisPayslip,
  ): void {
    xlsxPayslip.shopJournalInQBO =
      xlsxPayslip.totalPay == quickbooksPayslip.totalPay &&
      xlsxPayslip.employerNI == quickbooksPayslip.employerNI &&
      xlsxPayslip.employerPension == quickbooksPayslip.employerPension;
  }
}
