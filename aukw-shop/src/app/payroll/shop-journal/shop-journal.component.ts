import {
  Component,
  DestroyRef,
  inject,
  OnInit,
} from '@angular/core';
import { CommonModule, NgFor, NgIf } from '@angular/common';
import { IrisPayslip, PayrollProcessState } from '@app/_models';
import {
  AlertService,
  LoadingIndicatorService,
  QBEmployeeService,
  QBPayrollService,
  PayrollProcessStateService
} from '@app/_services';
import { forkJoin, map, of, shareReplay, Subject, takeUntil, tap } from 'rxjs';
import { environment } from '@environments/environment';
import { NgbTooltip } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'shop-journal',
  standalone: true,
  imports: [CommonModule, NgbTooltip, NgFor, NgIf],
  templateUrl: './shop-journal.component.html',
  styleUrls: ['./shop-journal.component.css', '../shared.css'],
})
export class ShopJournalComponent implements OnInit {
  lines: Array<IrisPayslip> = [];
  total: IrisPayslip = new IrisPayslip();

  payslips: IrisPayslip[] = [];
  payrollDate: string = '';

  private alertService = inject(AlertService);
  private qbPayrollService = inject(QBPayrollService);
  private loadingIndicatorService = inject(LoadingIndicatorService);
  private qbEmployeeService = inject(QBEmployeeService);
  private destroyRef = inject(DestroyRef);
  private stateService = inject(PayrollProcessStateService);

  ngOnInit() {
    const destroyed = new Subject();
    this.destroyRef.onDestroy(() => {
      destroyed.next('');
      destroyed.complete();
    });

    this.qbPayrollService.payslips$
      .pipe(
        takeUntil(destroyed),
        tap((response) => {
          this.payslips = response;
          this.payrollDate = response[0].payrollDate;
        }),
      )
      .subscribe(() => {
        this.recalculateEnterprisesTransactions();
      });
  }

  recalculateEnterprisesTransactions(): void {
    if (!this.payslips.length) return;

    this.total = new IrisPayslip(); // reset to zero

    forkJoin({
      payslips: of(this.payslips.filter((p) => p.isShopEmployee)),
      employees: this.qbEmployeeService.getAll(
        environment.qboEnterprisesRealmID,
      ),
    })
      .pipe(
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
        map((x: Array<IrisPayslip>) => {
          x.forEach((element) => {
            this.total.add(element);
          });
          return x;
        }),
        tap((x: Array<IrisPayslip>) => {}),
      )
      .subscribe((response) => (this.lines = response));
  }

  /**
   * Create a single new journal in the Enterprises Quickbooks file that records the salary, employer
   * NI and pension amounts for each shop employee.
   */
  createTransaction() {
    // Filter out lines for which there is already a QBO entry
    const linesToAdd = this.lines.filter((item) => {
      let ps = this.payslips.filter(
        (p) =>
          p.payrollNumber == item.payrollNumber &&
          (!p.qbFlags || !p.qbFlags.shopJournal),
      );
      return ps.length > 0;
    });

    if (linesToAdd && linesToAdd.length) {
      this.qbPayrollService
        .createShopJournal(this.lines, this.payrollDate)
        .pipe(
          this.loadingIndicatorService.createObserving({
            loading: () => `Adding journal to Enterprises Quickbooks`,
            success: (result) =>
              `Successfully created journal with id=${result.id} in Quickbooks.`,
            error: (err) => `${err}`,
          }),
          shareReplay(1),
        )
        .subscribe({
          error: (e) => {
            this.alertService.error(e, { autoClose: false });
          },
          complete: () => {
            this.qbPayrollService.sendPayslips(this.setQBOFlagsToTrue())
            this.stateService.setState(PayrollProcessState.EMPLOYERNI);
          }
        });
    } else {
      this.alertService.info(
        'There are no entries to add: they are all in Quickbooks already.',
      );
    }
  }

  /**
   * Check if the values contained in the given LineItemDetail have been flagged as having already been
   * entered in Quickbooks.
   * @param line The details of the entry
   * @returns 'True' if already in QBO.
   */
  inQBO(line: IrisPayslip): boolean {
    if (!this.payslips || !this.payslips.length) return false;
    return (
      this.payslips.filter(
        (p) => p.payrollNumber == line.payrollNumber && p.qbFlags.shopJournal,
      ).length != 0
    );
  }
  
  setQBOFlagsToTrue(){
    for(const payslip of this.payslips) {
      payslip.qbFlags.shopJournal = true;
    }
    return this.payslips;
  }
}
