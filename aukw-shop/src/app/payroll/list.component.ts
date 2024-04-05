import { Component, OnInit } from '@angular/core';
import { NgFor, NgIf } from '@angular/common';
import { Observable, from } from 'rxjs';
import { concatMap, filter, mergeMap, map } from 'rxjs/operators';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import {
  AlertService,
  AuthenticationService,
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
  employeeJournalEntries$!: Observable<PayrollJournalEntry[]>;

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
      filter((p:IrisPayslip) => p.employeeId == 34), //Test using Vesna
      filter(p => !p.payslipJournalInQBO), // Only add if not already in QBO
      map((p:IrisPayslip) => this.convertPayslipToQBOFormat(p)) // map into allocated classes
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
      )
      .subscribe({
        next: (response: any) => {
          this.allocations = response;
        },
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

    const employerNIArray: PayrollJournalEntry[] = [];

    this.payslips.forEach((payslip) => {
      const allocations = this.allocations.filter(
        (x) => x.payrollNumber == payslip.employeeId,
      );
      let sum: number = 0;
      if (allocations.length) {
        for (const [i, v] of allocations.entries()) {
          const entry = new PayrollJournalEntry({
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

    this.employeeJournalEntries$.pipe(
      mergeMap ((v) => this.qbPayrollService.createEmployeeJournal(
        this.charityRealm.realmid!,
        v,
        this.payrollDate
      ))
    ).subscribe((x:any) => console.log(x) );


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

  /**
   * Given an employee's patyslip numbers, convert them into an array that can be used
   * to create a journal in QBO.
   * @param p The detailed salary and deduction amounts form an employee's payslip
   * @returns 
   */
  convertPayslipToQBOFormat(p:IrisPayslip) : PayrollJournalEntry[] {
    const employeeEntry: PayrollJournalEntry[] = [];
        const allocations = this.allocations.filter(
          (x) => x.payrollNumber == p.employeeId,
        );
        let sum: number = 0;
        for (const [i, v] of allocations.entries()) {
          const entry = new PayrollJournalEntry({
            employeeId: v.id,
            class: v.class,
            account: null,
            description: 'Gross Salary',
            amount: Number(
              (Math.round(p.totalPay * v.percentage) / 100).toFixed(2),
            ),
          });

          // The sum of the allocated amounts must equal the starting total
          // If there is a discrepancy then adjust the final allocated amount.
          sum += entry.amount;
          if (i == allocations.length - 1 && sum != p.totalPay) {
            entry.amount += p.totalPay - sum;

            // Round to avoid numbers like 65.4000000000004
            entry.amount = Number(entry.amount.toFixed(2));
          }
          if (entry.amount) employeeEntry.push(entry);
        }

        // Now add all the deductions
        if (p.paye) employeeEntry.push(new PayrollJournalEntry({
          employeeId: allocations[0].id,
          description: 'PAYE',
          amount: p.paye
        }));
        if (p.employeeNI) employeeEntry.push(new PayrollJournalEntry({
          employeeId: allocations[0].id,
          description: 'Employee NI',
          amount: p.employeeNI
        }));
        if (p.salarySacrifice) employeeEntry.push(new PayrollJournalEntry({
          employeeId: allocations[0].id,
          description: 'Salary Sacrifice',
          amount: p.salarySacrifice
        }));
        if (p.studentLoan) employeeEntry.push(new PayrollJournalEntry({
          employeeId: allocations[0].id,
          description: 'Student Loan',
          amount: p.studentLoan
        }));        
        if (p.otherDeductions) employeeEntry.push(new PayrollJournalEntry({
          employeeId: allocations[0].id,
          description: 'Other Deductions',
          amount: p.otherDeductions
        }));

        // Now add the net pay side
        if (p.netPay) employeeEntry.push(new PayrollJournalEntry({
          employeeId: allocations[0].id,
          description: 'Net Pay',
          amount: p.netPay
        }));

        return employeeEntry;
  }
}
