import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
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
    this.employeeJournalsAdapter
      .createTransactions()
      .subscribe((response) => this.empJournalsSubject.next(response));

    this.enterprisesJournalsAdapter
      .createTransactions()
      .subscribe((response) => this.enterprisesSubject.next(response));

    this.pensionsJournalsAdapter.createTransactions().subscribe((response) => {
      this.pensionsSubject.next(response);
    });

    this.niJournalsAdapter.createTransactions().subscribe((response) => {
      this.employerniSubject.next(response);
    });
  }

  addToQuickBooks() {
    //this.employeeJournalsAdapter.addToQuickBooks();
    this.niJournalsAdapter.addToQuickBooks();
    //this.pensionsJournalsAdapter.addToQuickBooks();
    //this.enterprisesJournalsAdapter.addToQuickBooks();
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
        break;
    }
  }
}
