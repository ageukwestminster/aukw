import { Injectable } from '@angular/core';
import { BehaviorSubject, forkJoin, of, Subject, switchMap, tap } from 'rxjs';
import { IrisPayslip, LineItemDetail, PayrollJournalEntry } from '@app/_models';
import {
  EmployeeJournalsComponent,
  EmployerNiComponent,
  EnterprisesJournalComponent,
  PensionInvoiceComponent,
} from '@app/payroll/transactions';
import { PayrollIdentifier } from '@app/_interfaces/payroll-identifier';

@Injectable({
  providedIn: 'root',
})
export class PayrollTransactionsService {
  private employeeJournalsAdapter = new EmployeeJournalsComponent();
  private enterprisesJournalsAdapter = new EnterprisesJournalComponent();
  private pensionsJournalsAdapter = new PensionInvoiceComponent();
  private niJournalsAdapter = new EmployerNiComponent();

  private empJournalsSubject = new BehaviorSubject<PayrollJournalEntry[]>([]);
  private pensionsSubject = new BehaviorSubject<LineItemDetail[]>([]);
  private employerniSubject = new BehaviorSubject<LineItemDetail[]>([]);
  private enterprisesSubject = new BehaviorSubject<IrisPayslip[]>([]);
  private tceByClassSubject = new BehaviorSubject<[string, string, number][]>([]);

  employeejournals$ = this.empJournalsSubject.asObservable();
  pensions$ = this.pensionsSubject.asObservable();
  employerni$ = this.employerniSubject.asObservable();
  enterprises$ = this.enterprisesSubject.asObservable();
  tceByClass$ = this.tceByClassSubject.asObservable();

  createTransactions() {
    forkJoin({
      employee: this.employeeJournalsAdapter.createTransactions(),
      enterprises: this.enterprisesJournalsAdapter.createTransactions(),
      pensions: this.pensionsJournalsAdapter.createTransactions(),
      ni: this.niJournalsAdapter.createTransactions(),
    })
      .pipe(
        tap((x) => {
          this.empJournalsSubject.next(x.employee);
          this.enterprisesSubject.next(x.enterprises);
          this.pensionsSubject.next(x.pensions);
          this.employerniSubject.next(x.ni);
        }),

        // Calculate TCE by class
        switchMap((x) => {
          const output: [string, string, number][] = [];

          x.employee.forEach((payrollJournal) => {
            payrollJournal.totalPay.forEach((totalPayLine) => {
              var outputItem = output.find(
                (item) => item[0] === totalPayLine.class,
              );
              if (outputItem) {
                outputItem[2] += totalPayLine.amount;
              } else {
                output.push([totalPayLine.class, totalPayLine.className, totalPayLine.amount]);
              }
            });
          });

          // For pensions must filter out lines with Payroll Numbers 
          x.pensions
            .filter((x) => x.payrollNumber)
            .forEach((line) => {
              var outputItem = output.find((item) => item[0] === line.class);
              if (outputItem) {
                outputItem[2] += line.amount;
              } else {
                output.push([line.class, line.className, line.amount]);
              }
            });
            x.ni.forEach((line) => {
              var outputItem = output.find((item) => item[0] === line.class);
              if (outputItem) {
                outputItem[2] += line.amount;
              } else {
                output.push([line.class, line.className, line.amount]);
              }
            });
          output.sort((a,b)=> b[2]-a[2]);

          // Rename '01 Unrestricted' to 'Charity Shop'
          var shopClass = output.find((item) => item[1] === '01 Unrestricted');
          if (shopClass) shopClass[1] = 'Charity Shop';

          return of(output);
        }),
      )
      .subscribe((result) => this.tceByClassSubject.next(result));
  }

  addToQuickBooks() {
    this.employeeJournalsAdapter.addToQuickBooks();
    this.niJournalsAdapter.addToQuickBooks();
    this.pensionsJournalsAdapter.addToQuickBooks();
    this.enterprisesJournalsAdapter.addToQuickBooks();
  }

  inQBO(line: PayrollIdentifier, transactionType: string): boolean {
    switch (transactionType) {
      case 'EmployeeJournals':
        return this.employeeJournalsAdapter.inQBO(line);
      case 'Enterprises':
        return this.enterprisesJournalsAdapter.inQBO(line);
      case 'Pensions':
        return this.pensionsJournalsAdapter.inQBO(line);
      case 'EmployerNI':
        return this.niJournalsAdapter.inQBO(line);
      default:
        return false;
    }
  }
}
