import { Component, OnInit, inject } from '@angular/core';
import { RouterLink } from '@angular/router';
import { NgFor, NgIf } from '@angular/common';
import { Observable, forkJoin, BehaviorSubject } from 'rxjs';
import {
  concatMap,
  filter,
  map,
  mergeMap,
  retry,
  scan,
  tap,
  toArray,
} from 'rxjs/operators';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import {
  AlertService,
  AuthenticationService,
  ConsoleService,
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

  qboAuthorisationMissing: boolean = false;

  loading$: BehaviorSubject<boolean>;
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

  consoleMessage$: Observable<string>;

  private alertService = inject(AlertService);
  private qbRealmService = inject(QBRealmService);
  private authenticationService = inject(AuthenticationService);
  private qbPayrollService = inject(QBPayrollService);
  private payrollService = inject(PayrollService);
  private qbEmployeeService = inject(QBEmployeeService);
  private consoleService = inject(ConsoleService);

  constructor() {
    this.user = this.authenticationService.userValue;
    this.loading$ = new BehaviorSubject<boolean>(false);
    this.consoleMessage$ = this.consoleService.consoleMessage$.pipe(
      scan(
        (accumultor, message) =>
          accumultor + (message == '.' ? '.' : '\n' + message),
      ),
    );
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
            item.isShopEmployee && item.payrollNumber == payslip.payrollNumber,
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
    this.loading$.next(true);

    this.qbRealmService
      .getAll(this.user.id)
      .pipe(
        tap(() =>
          this.consoleService.sendConsoleMessage(
            'Loading Quickbooks' +
              ' connection details for Charity and Enterprises.',
          ),
        ),
        concatMap((realms: QBRealm[]) => {
          realms.forEach((r: QBRealm) => {
            if (!r.connection || !r.connection.refreshtoken) {
              this.qboAuthorisationMissing = true;
            } else {
              this.consoleService.sendConsoleMessage(
                `Quickbooks connection to Company file '${r.name}' found.`,
              );
            }

            if (!r.issandbox && r.name) {
              if (/enterprises/i.test(r.name)) {
                this.enterpriseRealm = r;
              } else {
                this.charityRealm = r;
              }
            }
          });

          this.consoleService.sendConsoleMessage(
            'Loading employee allocations from Quickbooks:',
          );

          if (
            this.charityRealm &&
            this.charityRealm.connection &&
            this.enterpriseRealm &&
            this.enterpriseRealm.connection
          ) {
            return this.qbPayrollService
              .getAllocations()
              .pipe(retry(2));
          } else {
            // Flag that user needs to authorise this app in QBO
            this.qboAuthorisationMissing = true;
            throw new Error('This app is not authorised in QBO.');
          }
        }),
        tap((allocations) => {
          this.allocations = allocations;
          this.consoleService.sendAllocationsToConsole(allocations);
          this.consoleService.sendConsoleMessage(
            '\n' +
              "Press 'File Upload' to begin processing payroll spreadsheet.",
          );
          this.loading$.next(false);
        }),
      )
      .subscribe({
        error: (error: any) => {
          this.alertService.error(error);
          this.loading$.next(false);
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

  /**
   * Interrogate Quickbooks (both charity and enterprises) to see if the monthly payroll transactions
   * have already been booked.
   * @returns void
   */
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
    this.loading$.next(true);

    // Reset disablement flags
    this.disableEmployerNI = true;
    this.disableEmployeeJournals = true;
    this.disablePensions = true;
    this.disableShopJournals = true;

    // Time added to avoid problems with BST
    const dt = new Date(this.payrollDate + 'T12:00:00');
    const month = (dt.getMonth() + 1).toString().padStart(2, '0');
    const year = dt.getFullYear().toString();

    this.consoleService.sendConsoleMessage(
      `Querying Quickbooks for existing payroll transactions for ${year}-${month}.`,
    );

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
        tap((x) => {
          this.consoleService.sendConsoleMessage(
            `${'\n'}Entries found in Charity Quickbooks: `,
          );
          this.consoleService.sendPayslipsToConsole(x.charityPayslips);
          this.consoleService.sendConsoleMessage(
            `${'\n'}Entries found in Enterprises Quickbooks: `,
          );
          this.consoleService.sendShopPayslipsToConsole(x.shopPayslips);
        }),
        map((x) => {
          this.payslips.forEach((xlsxPayslip) => {
            let qbPayslip = x.charityPayslips.find(
              (item) => item.payrollNumber == xlsxPayslip.payrollNumber,
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
              (item) => item.payrollNumber == xlsxPayslip.payrollNumber,
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
        error: (e) => {
          this.alertService.error(e);
          this.loading$.next(false);
        },
        complete: () => {
          this.loading$.next(false);
          this.disableEmployerNI = false;
          this.disableEmployeeJournals = false;
          this.disablePensions = false;
          this.disableShopJournals = false;
        },
      });
  }

  /**
   * Add the general ledger that contains details of the costs of employer NI to the QBO company file.
   *
   * This ledger has a line for each employee detailing the employer NI cost. These amounts might be
   * split between two or more classes. Employees who have zero employer NI are absent.
   * @returns void
   */
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
        toArray(), //concat them into an array
        filter((journalLines) => journalLines && journalLines.length > 0), // only proceed if array is not empty
        tap((journalLines) =>
          this.consoleService.sendLineItemDetailToConsole(journalLines),
        ), //Send to console
        mergeMap((allocatedEmployerNICosts) =>
          // Create the GJ entry in Charity QBO
          this.qbPayrollService.createEmployerNIJournal(
            allocatedEmployerNICosts,
            this.payrollDate,
          ),
        ),
      )
      .subscribe({
        next: () => this.alertService.info('Employer NI journal added.'),
        error: (e) => {
          this.alertService.error(e, { autoClose: false });
          this.busyOnEmployerNI = false;
        },
        complete: () => {
          this.busyOnEmployerNI = false;
          this.disableEmployerNI = true;
          this.consoleService.sendConsoleMessage(
            `${'\n'}Employer NI added to Quickbooks.`,
          );
        },
      });
  }

  /**
   * Add a general ledger for each employee contains details salary and deductions to the QBO company file.
   *
   * This ledger may have multiple lines for detailing the total salary. These amounts might be
   * split between two or more classes.
   * @returns void
   */
  makeEmployeeJournalEntries() {
    this.busyOnEmployeeJournals = true;

    this.payrollService
      .employeeJournalEntries(this.payslips, this.allocations)
      .pipe(
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
          this.alertService.error(e, { autoClose: false });
          this.busyOnEmployeeJournals = false;
        },
        complete: () => {
          this.busyOnEmployeeJournals = false;
          this.disableEmployeeJournals = true;
        },
      });
  }

  /**
   * Add the pension invoice payable to Legal & General, our pensions provider, to the QBO company file.
   *
   * This bill has a line for total of salary sacrifice, a line for total of employee pension contributions
   * and lines for each employee detailing the employer pension contribution. These final amounts might be
   * split between two or more classes. Employees who are not in the pension scheme are absent.
   * @returns void
   */
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
          this.alertService.error(e, { autoClose: false });
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
            // Find the employee that matches the payslip
            const employeeName = x.employees.filter(
              (emp) => emp.payrollNumber == payslip.payrollNumber,
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
          this.consoleService.sendConsoleMessage(
            `${'\n'}Enterprises transactions added to Quickbooks.`,
          );
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
