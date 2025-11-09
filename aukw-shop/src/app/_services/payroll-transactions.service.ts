import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { IrisPayslip, LineItemDetail, PayrollJournalEntry } from '@app/_models';
import { EmployeeJournalsComponent, EnterprisesJournalComponent } from '@app/payroll2/transactions';
import { PayrollIdentifier } from '@app/_interfaces/payroll-identifier';

@Injectable({
  providedIn: 'root',
})
export class PayrollTransactionsService {
  private employeeJournalsAdapter = new EmployeeJournalsComponent();
  private enterprisesJournalsAdapter = new EnterprisesJournalComponent();

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
  }

  addToQuickBooks() {
    this.employeeJournalsAdapter.addToQuickBooks();
    this.enterprisesJournalsAdapter.addToQuickBooks();
  }

  inQBO(line: PayrollIdentifier, transactionType: string): boolean {
    switch (transactionType) {
      case 'EmployeeJournals':
        return this.employeeJournalsAdapter.inQBO(line);
      case 'Enterprises':  
         return this.enterprisesJournalsAdapter.inQBO(line);
      default:
        return false;
        break;
    }
    
  }
}
