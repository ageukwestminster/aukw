import { Component, OnInit } from '@angular/core';
import { RouterLink } from '@angular/router';
import { NgFor, NgIf } from '@angular/common';
import { Observable, forkJoin } from 'rxjs';
import {
  concatMap,
  filter,
  map,
  mergeMap,
  retry,
  tap,
  toArray,
} from 'rxjs/operators';
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
  QBFlags,
  QBRealm,
  User,
} from '@app/_models';
import { SharedModule } from '@app/shared/shared.module';

@Component({
  templateUrl: 'list.component.html',
  standalone: true,
  imports: [NgFor, NgIf, NgbModule, RouterLink, SharedModule],
  styleUrls: ['list.component.css'],
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

  initializing: boolean = false;
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

  cummulativeQbFlags: QBFlags = new QBFlags();

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

      // Set flag for shop employees according to the allocations array values
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
    this.initializing = true;
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
            return this.qbPayrollService
              .getAllocations(this.charityRealm.realmid!)
              .pipe(retry(2));
          } else {
            // Flag that user needs to authorise this app in QBO
            this.qboAuthorisationMissing = true;
            throw new Error('This app is not authorised in QBO.');
          }
        }),
        tap((allocations) => {
          this.allocations = allocations;
          this.initializing = false;
        }),
      )
      .subscribe({
        error: (error: any) => {
          this.alertService.error(error);
          this.initializing = false;
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
      !this.payslips ||
      !this.payslips[0] ||
      !this.charityRealm.realmid ||
      !this.enterpriseRealm.realmid
    ) {
      return;
    }

    // Inform user background work is happening
    this.loading = true;

    // Reset disablement flags
    this.disableEmployerNI = false;
    this.disableEmployeeJournals = false;
    this.disablePensions = false;
    this.disableShopJournals = false;

    // Time added to avoid problems with BST
    const dt = new Date(this.payrollDate + 'T12:00:00');
    const month = (dt.getMonth() + 1).toString().padStart(2, '0');
    const year = dt.getFullYear().toString();

    // forkJoin will wait for both Observables to complete
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
          this.payslips.forEach((xlsxPayslip) => {
            let qbPayslip = x.charityPayslips.find(
              (item) => item.employeeId == xlsxPayslip.employeeId,
            );
            qbPayslip = qbPayslip ?? new IrisPayslip();

            xlsxPayslip.niJournalInQBO = this.isEqualEmployerNI(
              xlsxPayslip,
              qbPayslip,
            );
            xlsxPayslip.pensionBillInQBO = this.isEqualPension(
              xlsxPayslip,
              qbPayslip,
            );
            xlsxPayslip.payslipJournalInQBO = this.isEqualPay(
              xlsxPayslip,
              qbPayslip,
            );

            const qbShopPayslip = x.shopPayslips.find(
              (item) => item.employeeId == xlsxPayslip.employeeId,
            );

            xlsxPayslip.shopJournalInQBO = this.isEqualShopPay(
              xlsxPayslip,
              qbShopPayslip ?? new IrisPayslip(),
            );
          });
        }),
        tap(() => {
          // if there are any payslips with non-zero Employer NI and which have not
          // been added yet to QBO then set the cummulative flag.
          this.cummulativeQbFlags.niJournalInQBO =
            this.payslips.filter(
              (payslip) => payslip.employerNI && !payslip.niJournalInQBO,
            ).length == 0;

          // Now compute payslip flag
          this.cummulativeQbFlags.payslipJournalInQBO =
            this.payslips.filter((payslip) => !payslip.payslipJournalInQBO)
              .length == 0;

          // if there are any payslips with non-zero Employer pension and which
          // have not been added yet to QBO then set the cummulative flag.
          this.cummulativeQbFlags.pensionBillInQBO =
            this.payslips.filter(
              (payslip) => payslip.employerPension && !payslip.pensionBillInQBO,
            ).length == 0;

          // if there are shop employees for which the Enterprises journal is
          // not yet done then set the cummulative flag.
          this.cummulativeQbFlags.shopJournalInQBO =
            this.payslips.filter(
              (payslip) => payslip.isShopEmployee && !payslip.shopJournalInQBO,
            ).length == 0;
        }),
      )
      .subscribe({
        error: (e) => this.alertService.error(e),
        complete: () => {
          this.loading = false;
        },
      });
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
        filter((allocations) => allocations && allocations.length > 0),
        mergeMap((allocatedEmployerNICosts) =>
          this.qbPayrollService.createEmployerNIJournal(
            this.charityRealm.realmid!,
            allocatedEmployerNICosts,
            this.payrollDate,
          ),
        ),
      )
      .subscribe({
        next: () => this.alertService.info('Employer NI journal added.'),
        error: (e) => {
            this.alertService.error(e, {autoClose: false});
            this.busyOnEmployerNI = false;
        },
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
        error: (e) => {
          this.alertService.error(e, {autoClose: false});
          this.busyOnEmployeeJournals = false;
          this.showProgressBar = false;
      },
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
          this.alertService.error(e, {autoClose: false});
          this.busyOnPensions = false;
        },
        complete: () => {
          this.busyOnPensions = false;
          this.disablePensions = true;
        },
      });
  }

  /**
   * Add the payroll journal in the shop QBO company file. This journal has salary,
   * NI and pension amounts for each shop employee.
   * @returns void
   */
  enterPayrollJournalInEnterprisesCompany(): void {
    if (!this.payslips || !this.payslips.length) return;

    this.busyOnShopJournals = true;

    //forkJoin waits for both Observables to complete before proceeding
    forkJoin({
      // payslips for shop employees, but only if not already entered.
      payslips: this.payrollService
        .shopPayslips(this.payslips, this.allocations)
        .pipe(toArray()), // return an array, not one-by-one

      // A list of employees in the Enterprises QBO file.
      employees: this.qbEmployeeService.getAll(this.enterpriseRealm!.realmid!),
    })
      .pipe(
        map((x) => {
          let returnArray: Array<{
            quickbooksId: number;
            totalPay: number;
            employerNI: number;
            employerPension: number;
          }> = [];

          // The quickbooks
          x.payslips.forEach((payslip) => {
            // Find th eemployee that matches the payslip
            const employeeName = x.employees.filter(
              (emp) => emp.payrollNumber == payslip.employeeId,
            )[0];

            // This data will go to the API
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
        next: () => {
          this.alertService.info(' Shop payroll journal added.');
        },
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

  /**
   * Compare the payslip calculated from the Iris spreadsheet with the payslip
   * calculated from QB values and see if they are equal, considering only the properties
   * that matter for calculating the journal entries needed for the charity QBO.
   * Return 'true' if they match
   * @param xlsxPayslip The payslip calculated from the Iris spreadsheet
   * @param quickbooksPayslip The payslip calculated from QB values
   * @returns 'true' if they are equal
   */
  isEqualPay(
    xlsxPayslip: IrisPayslip,
    quickbooksPayslip: IrisPayslip,
  ): boolean {
    return (
      xlsxPayslip.totalPay == quickbooksPayslip.totalPay &&
      xlsxPayslip.paye == quickbooksPayslip.paye &&
      xlsxPayslip.employeeNI == quickbooksPayslip.employeeNI &&
      xlsxPayslip.otherDeductions == quickbooksPayslip.otherDeductions &&
      xlsxPayslip.employeePension == quickbooksPayslip.employeePension &&
      xlsxPayslip.salarySacrifice == quickbooksPayslip.salarySacrifice &&
      xlsxPayslip.studentLoan == quickbooksPayslip.studentLoan &&
      xlsxPayslip.netPay == quickbooksPayslip.netPay
    );
  }

  /**
   * Compare the payslip calculated from the Iris spreadsheet with the payslip
   * calculated from QB values and see if they are equal, considering only Employer pension.
   * Return 'true' if they match
   * @param xlsxPayslip The payslip calculated from the Iris spreadsheet
   * @param quickbooksPayslip The payslip calculated from QB values
   * @returns 'true' if they are equal
   */
  isEqualPension(
    xlsxPayslip: IrisPayslip,
    quickbooksPayslip: IrisPayslip,
  ): boolean {
    return (
      xlsxPayslip.employerPension == 0 ||
      xlsxPayslip.employerPension == quickbooksPayslip.employerPension
    );
  }

  /**
   * Compare the payslip calculated from the Iris spreadsheet with the payslip
   * calculated from QB values and see if they are equal, considering only Employer NI.
   * Return 'true' if they match
   * @param xlsxPayslip The payslip calculated from the Iris spreadsheet
   * @param quickbooksPayslip The payslip calculated from QB values
   * @returns 'true' if they are equal
   */
  isEqualEmployerNI(
    xlsxPayslip: IrisPayslip,
    quickbooksPayslip: IrisPayslip,
  ): boolean {
    return (
      xlsxPayslip.employerNI == 0 ||
      xlsxPayslip.employerNI == quickbooksPayslip.employerNI
    );
  }

  /**
   * Compare the payslip calculated from the Iris spreadsheet with the payslip
   * calculated from QB values and see if they are equal, considering only the properties
   * that matter for calculating the journal entries needed for the Enterprises QBO.
   * Return 'true' if they match
   * @param xlsxPayslip The payslip calculated from the Iris spreadsheet
   * @param quickbooksPayslip The payslip calculated from QB values
   * @returns 'true' if they are equal
   */
  isEqualShopPay(
    xlsxPayslip: IrisPayslip,
    quickbooksPayslip: IrisPayslip,
  ): boolean {
    return (
      (xlsxPayslip.totalPay == 0 &&
        xlsxPayslip.employerNI == 0 &&
        xlsxPayslip.employerPension == 0) ||
      (xlsxPayslip.totalPay == quickbooksPayslip.totalPay &&
        xlsxPayslip.employerNI == quickbooksPayslip.employerNI &&
        xlsxPayslip.employerPension == quickbooksPayslip.employerPension)
    );
  }
}
