import {
  Component,
  inject,
  DestroyRef,
  OnInit,
} from '@angular/core';
import { CommonModule, NgFor, NgIf } from '@angular/common';
import {
  EmployeeAllocation,
  IrisPayslip,
  PayrollJournalEntry,
  PayrollProcessState,
  QBTransactionFlags,
} from '@app/_models';
import {
  AlertService,
  LoadingIndicatorService,
  QBPayrollService,
  PayrollService,
  PayrollProcessStateService
} from '@app/_services';
import {
  from,
  mergeMap,
  Subject,
  shareReplay,
  tap,
  takeUntil,
  toArray,
} from 'rxjs';
import { NgbTooltip } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'employee-journals',
  standalone: true,
  imports: [CommonModule, NgbTooltip, NgFor, NgIf],
  templateUrl: './employee-journals.component.html',
  styleUrls: ['./employee-journals.component.css', '../shared.css'],
})
export class EmployeeJournalsComponent implements OnInit {
  lines: PayrollJournalEntry[] = [];

  allocations: EmployeeAllocation[] = [];
  payslips: IrisPayslip[] = [];
  payrollDate: string = '';

  private loadingIndicatorService = inject(LoadingIndicatorService);
  private payrollService = inject(PayrollService);
  private qbPayrollService = inject(QBPayrollService);
  private alertService = inject(AlertService);
  private destroyRef = inject(DestroyRef);
  private stateService = inject(PayrollProcessStateService);

  ngOnInit() {
    const destroyed = new Subject();
    this.destroyRef.onDestroy(() => {
      destroyed.next('');
      destroyed.complete();
    });

    this.qbPayrollService.allocations$
      .pipe(takeUntil(destroyed))
      .subscribe((response) => (this.allocations = response));

    this.qbPayrollService.payslips$
      .pipe(
        takeUntil(destroyed),
        tap((response) => {
          this.payslips = response;
          this.payrollDate = response[0].payrollDate;
        }),
      )
      .subscribe(() => {
        this.recalculateJournalEntries();
      });
  }

  recalculateJournalEntries() {
    if (!this.payslips.length) return;

    this.payrollService
      .employeeJournalEntries(this.payslips, this.allocations)
      .pipe(toArray())
      .subscribe({
        next: (response) => (this.lines = response),
        error: (e) => {
          this.alertService.error(e, { autoClose: false });
        },
      });
  }

  /**
   * Create a set of new journals in the Charity Quickbooks file that records the salary,
   * deductions and net pay amounts for each employee.
   */
  createTransaction() {
    // Filter out lines for which there is already a QBO entry
    const journalsToAdd = this.lines.filter((item) => {
      let ps = this.payslips.filter(
        (p) =>
          p.payrollNumber == item.payrollNumber &&
          (!p.qbFlags || !p.qbFlags.employeeJournal),
      );
      return ps.length > 0;
    });

    if (journalsToAdd && journalsToAdd.length) {
      from(journalsToAdd)
        .pipe(
          mergeMap((j) =>
            this.qbPayrollService.createEmployeeJournal(j, this.payrollDate),
          ),
          toArray(),
          this.loadingIndicatorService.createObserving({
            loading: () => `Adding employee journals to Charity Quickbooks`,
            success: (result) =>
              `Successfully created ${result.length} journals in Quickbooks.`,
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
            this.stateService.setState(PayrollProcessState.JOURNALS);
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
  inQBO(line: any): boolean {
    if (!this.payslips || !this.payslips.length) return false;
    return (
      this.payslips.filter(
        (p) =>
          p.payrollNumber == line.payrollNumber && p.qbFlags.employeeJournal,
      ).length != 0
    );
  }

  setQBOFlagsToTrue(){
    for(const payslip of this.payslips) {
      payslip.qbFlags.employeeJournal = true;
    }
    return this.payslips;
  }
}
