import { Injectable } from '@angular/core';
import { BehaviorSubject, forkJoin, tap } from 'rxjs';
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

  employeejournals$ = this.empJournalsSubject.asObservable();
  pensions$ = this.pensionsSubject.asObservable();
  employerni$ = this.employerniSubject.asObservable();
  enterprises$ = this.enterprisesSubject.asObservable();

  createTransactions() {
    forkJoin({
      employee: this.employeeJournalsAdapter.createTransactions(),
      enterprises: this.enterprisesJournalsAdapter.createTransactions(),
      pensions: this.pensionsJournalsAdapter.createTransactions(),
      ni: this.niJournalsAdapter.createTransactions(),
    }).pipe (
      tap(x => {
        this.empJournalsSubject.next(x.employee);
        this.enterprisesSubject.next(x.enterprises);
        this.pensionsSubject.next(x.pensions);
        this.employerniSubject.next(x.ni);
      })
    ).subscribe();
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
