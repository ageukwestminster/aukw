import { Component, inject } from '@angular/core';
import { IrisPayslip, PayrollProcessState } from '@app/_models';
import { QBEmployeeService } from '@app/_services';
import { forkJoin, map, Observable, of, shareReplay, tap } from 'rxjs';
import { environment } from '@environments/environment';
import { BasePayrollTransactionComponent } from './base-transaction.component';

@Component({
  selector: 'enterprises-journals',
  standalone: true,
  imports: [],
  template: '',
})
export class EnterprisesJournalComponent extends BasePayrollTransactionComponent<IrisPayslip> {
  total: IrisPayslip = new IrisPayslip();

  private qbEmployeeService = inject(QBEmployeeService);

  override createTransactions(): Observable<IrisPayslip[]> {
    if (!this.payslips.length) return of([]);

    this.total = new IrisPayslip(); // reset to zero

    return forkJoin({
      payslips: of(this.payslips.filter((p) => p.isShopEmployee)),
      employees: this.qbEmployeeService.getAll(
        environment.qboEnterprisesRealmID,
      ),
    }).pipe(
      map((x) => {
        let returnArray: Array<IrisPayslip> = [];

        x.payslips.forEach((payslip) => {
          // Find the employee that matches the payslip
          const employeeName = x.employees.filter(
            (emp) => emp.payrollNumber == payslip.payrollNumber,
          )[0];

          // This data will go to the API
          returnArray.push(
            new IrisPayslip({
              payrollNumber: payslip.payrollNumber,
              quickbooksId: employeeName.quickbooksId,
              employeeName: employeeName.name,
              totalPay: payslip.totalPay,
              employerNI: payslip.employerNI,
              employerPension: payslip.employerPension,
            }),
          );
        });

        return returnArray;
      }),
      map((x: IrisPayslip[]) => {
        x.forEach((element) => {
          this.total.add(element);
        });
        return x;
      }),
    );
  }

  /**
   * Create a single new journal in the Enterprises QuickBooks file that records the salary, employer
   * NI and pension amounts for each shop employee.
   */
  addToQuickBooks() {
    // Filter out lines for which there is already a QBO entry
    const filteredTransactions = this.filteredTransactions(
      this.getQBFlagsProperty(),
    );

    if (filteredTransactions && filteredTransactions.length) {
      this.qbPayrollService
        .createShopJournal(this.lines, this.payrollDate)
        .pipe(
          this.loadingIndicatorService.createObserving({
            loading: () => `Adding journal to Enterprises QuickBooks`,
            success: (result) =>
              `Successfully created journal with id=${result.id} in QuickBooks.`,
            error: (err) => `${err}`,
          }),
          shareReplay(1),

          // Add entry to audit log
          tap((result) => {
            this.auditLogService.log(
              this.authenticationService.userValue,
              'INSERT',
              `Added shop employees journal (with id=${result.id}) to Enterprises QuickBooks`,
              'General Journal',
              result.id,
            );
          }),
        )
        .subscribe({
          error: (e) => {
            this.alertService.error(e, { autoClose: false });
          },
          complete: () => {
            this.qbPayrollService.sendPayslips(this.setQBOFlagsToTrue());
            this.stateService.setState(PayrollProcessState.EMPLOYERNI);
          },
        });
    } else {
      this.alertService.info(
        'There are no entries to add: they are all in QuickBooks already.',
      );
    }
  }

  /** This is the property that the list must check to see if the line is in QBO or not*/
  override getQBFlagsProperty() {
    return function (payslip: IrisPayslip) {
      return payslip.shopJournalInQBO;
    };
  }
  override setQBFlagsProperty() {
    return function (payslip: IrisPayslip, value: boolean) {
      payslip.shopJournalInQBO = value;
    };
  }
}
