import { Component, OnInit } from '@angular/core';
import { NgFor, NgIf } from '@angular/common';
import { Observable, from } from 'rxjs';
import {
  concatMap,
  filter,
  mergeMap,
  map,
  scan,
  tap,
  toArray,
} from 'rxjs/operators';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import {
  AlertService,
  AuthenticationService,
  PayrollService,
  QBPayrollService,
  QBRealmService,
} from '@app/_services';
import {
  EmployeeAllocation,
  EmployerNIEntry,
  PayrollJournalEntry,
  IrisPayslip,
  PensionAllocation,
  QBRealm,
  User,
  TotalPayAllocation,
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

  constructor(
    private alertService: AlertService,
    private qbRealmService: QBRealmService,
    private authenticationService: AuthenticationService,
    private qbPayrollService: QBPayrollService,
    private payrollService: PayrollService,
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

    const employerNIArray: EmployerNIEntry[] = [];

    this.payslips.forEach((payslip) => {
      const allocations = this.allocations.filter(
        (x) => x.payrollNumber == payslip.employeeId,
      );
      let sum: number = 0;
      if (allocations.length) {
        for (const [i, v] of allocations.entries()) {
          const entry = new EmployerNIEntry({
            employeeId: v.id,
            class: v.class,
            account: v.account,
            amount: Number(
              (Math.round(payslip.employerNI * v.percentage) / 100).toFixed(2),
            ),
          });

          // The sum of the allocated amounts must equal the starting total
          // If there is a discrepancy then adjust the final allocated amount.
          sum += entry.amount;
          if (i == allocations.length - 1 && sum != payslip.employerNI) {
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
      .createEmployerNIJournal(
        this.charityRealm.realmid!,
        employerNIArray,
        this.payrollDate,
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

    const costs$ = from(this.payslips)
      .pipe(
        filter((p) => !p.pensionBillInQBO && p.employerPension != 0), // Only add if not already in QBO
        concatMap((p) =>
          // this will split each payslip into one or more allocations
          from(
            this.allocations.filter((x) => x.payrollNumber == p.employeeId),
          ).pipe(
            // Ignore any allocations of 0%
            filter((x) => x.percentage != 0),

            // loop through each allocation, computing the correct £ amount from the percentage supplied
            scan(
              (acc: any, allocation: EmployeeAllocation) => {
                // We are looping through an array of allocations ordered by employee
                // When the employee changes, reset subtotal (i.e. sum) to zero
                if (allocation.id != acc.entry.id) {
                  acc.sum = 0;
                }

                // This is what's left to allocate
                const remainder = p.employerPension - acc.sum;

                // Make first attempt at calcualted amount, from percentage and pension amount
                let calculatedAllocatedAmount = Number(
                  (
                    Math.round(p.employerPension * allocation.percentage) / 100
                  ).toFixed(2),
                );

                // Amount can never exceed remainder
                calculatedAllocatedAmount = Math.min(
                  calculatedAllocatedAmount,
                  remainder,
                );

                // Handle edge case: Calculated amount is close to but less than remainder
                // In that case then use the remainder
                if (Math.abs(remainder - calculatedAllocatedAmount) < 1)
                  calculatedAllocatedAmount = remainder;

                const pa = new PensionAllocation({
                  id: allocation.id,
                  name: allocation.name,
                  account: allocation.account,
                  class: allocation.class,
                  amount: calculatedAllocatedAmount,
                });

                // Send this object back as an accumulator, later we will just take the entry property
                return {
                  sum: acc.sum + pa.amount,
                  entry: pa,
                };
              },
              // starting value for accumulator
              {
                sum: 0,
                entry: new PensionAllocation({ id: 0, amount: 0 }),
              },
            ),
          ),
        ),
        map((x) => x.entry), // pluck a single property
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
        error: (e) => this.alertService.error(e),
        complete: () => (this.busyOnPensions = false),
      });
  }

}
