import { Component, OnInit } from '@angular/core';
import { NgFor, NgIf } from '@angular/common';
import { Observable, from } from 'rxjs';
import { concatMap, filter, mergeMap, map, scan, tap, toArray } from 'rxjs/operators';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import {
  AlertService,
  AuthenticationService,
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
  employeePensionCosts$!: Observable<PensionAllocation[]>;

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

    this.employeeJournalEntries$ = from(this.payslips).pipe(
      filter((p) => !p.payslipJournalInQBO), // Only add if not already in QBO
      map((p: IrisPayslip) => this.convertPayslipToQBOFormat(p)), // map into allocated classes
      tap(() => this.currentPayslip++), // used to fill progress bar
    );
/*
    this.employeePensionCosts$ = from(this.payslips).pipe(
      filter((p) => !p.pensionBillInQBO), // Only add if not already in QBO
      scan((sum,current) => {
        return sum+current.employeePension;
      })),
      toArray()
    )*/

    this.employeePensionCosts$ = from(this.allocations).pipe(
      map((alloc) => {
        
        const pensionCost = this.payslips.filter(
          (p) => p.employeeId == alloc.payrollNumber,
        )[0].employerPension;

        return new PensionAllocation({
          name: alloc.name,
          account: alloc.account,
          class: alloc.class,
          amount: Number(
            (Math.round(pensionCost * alloc.percentage) / 100).toFixed(2),
          ),
        });
      }),
      toArray(),
    );
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
      .subscribe((x: any) => {
        console.log(x);
      });
  }

  makeEmployeeJournalEntries() {
    this.currentPayslip = 0;
    this.showProgressBar = true;

    this.employeeJournalEntries$
      .pipe(
        mergeMap((v) =>
          this.qbPayrollService.createEmployeeJournal(
            this.charityRealm.realmid!,
            v,
            this.payrollDate,
          ),
        ),
      )
      .subscribe(() => (this.showProgressBar = false));
  }

  pensionBill() {
    if (!this.payslips || !this.payslips.length) return;

    this.employeePensionCosts$.pipe(
      mergeMap((costs) => {
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
    );

    this.qbPayrollService
      .createPensionBill(
        this.charityRealm.realmid!,
        {
          salarySacrificeTotal: this.total.salarySacrifice.toFixed(2),
          employeePensionTotal: this.total.employeePension.toFixed(2),
          total: (
            this.total.employeePension +
            this.total.salarySacrifice +
            this.total.employerPension
          ).toFixed(2),
        },
        this.payrollDate,
      )
      .subscribe((x) => console.log(x));
  }

  /**
   * Given an employee's payslip numbers, convert them into an array that can be used
   * to create a journal in QBO.
   * @param p The detailed salary and deduction amounts form an employee's payslip
   * @returns
   */
  convertPayslipToQBOFormat(p: IrisPayslip): PayrollJournalEntry {
    const allocations = this.allocations.filter(
      (allocation) => allocation.payrollNumber == p.employeeId,
    );

    const entry = new PayrollJournalEntry({
      employeeId: allocations[0].id,
      totalPay: [],
      paye: p.paye,
      employeeNI: p.employeeNI,
      otherDeductions: p.otherDeductions,
      salarySacrifice: -p.salarySacrifice,
      employeePension: -p.employeePension,
      studentLoan: p.studentLoan,
      netPay: -p.netPay,
      name: p.employeeName,
    });

    let sum: number = 0;
    for (const [i, v] of allocations.entries()) {
      const alloc = new TotalPayAllocation({
        class: v.class,
        account: v.account,
        amount: Number(
          (Math.round(p.totalPay * v.percentage) / 100).toFixed(2),
        ),
      });

      // The sum of the allocated amounts must equal the starting total
      // If there is a discrepancy then adjust the final allocated amount.
      sum += alloc.amount;
      if (i == allocations.length - 1 && sum != p.totalPay) {
        alloc.amount += p.totalPay - sum;

        // Round to avoid numbers like 65.4000000000004
        alloc.amount = Number(alloc.amount.toFixed(2));
      }
      if (alloc.amount) entry.totalPay.push(alloc);
    }

    return entry;
  }
}
