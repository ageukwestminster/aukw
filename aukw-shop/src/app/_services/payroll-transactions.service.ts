import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { IrisPayslip, LineItemDetail, PayrollJournalEntry } from '@app/_models';
import { EmployeeJournalsComponent } from '../payroll2/transactions/employee-journals.component';

@Injectable({
  providedIn: 'root'
})
export class PayrollTransactionsService {

  private employeeJournalsAdapter = new EmployeeJournalsComponent();


  private employeejournalsSubject = new BehaviorSubject<PayrollJournalEntry[]>([]);
  private pensionsSubject = new BehaviorSubject<LineItemDetail[]>([]);
  private employerniSubject = new BehaviorSubject<LineItemDetail[]>([]);
  private enterprisesSubject = new BehaviorSubject<LineItemDetail[]>([]);

  employeejournals$ = this.employeejournalsSubject.asObservable();
  pensions$ = this.pensionsSubject.asObservable();
  employerni$ = this.employerniSubject.asObservable();
  enterprises$ = this.enterprisesSubject.asObservable();

  createTransactions() {
    this.employeeJournalsAdapter.createTransactions().subscribe(response => 
      this.employeejournalsSubject.next(response)
    )
  }

}
