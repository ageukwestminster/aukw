import {
  Component,
  inject,
  Input,
  OnChanges,
  SimpleChanges,
} from '@angular/core';
import { CommonModule, NgFor, NgIf } from '@angular/common';
import { EmployeeAllocation, IrisPayslip, LineItemDetail } from '@app/_models';
import {
  AlertService,
  LoadingIndicatorService,
  PayrollService,
  QBPayrollService,
} from '@app/_services';
import { shareReplay, scan, tap } from 'rxjs';
import { PayrollEntryTypeDetails } from '../payroll-entry.model';
import { NgbTooltip } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'transaction-entry',
  standalone: true,
  imports: [CommonModule, NgbTooltip, NgFor, NgIf],
  templateUrl: './transaction-entry.component.html',
})
export class TransactionEntryComponent implements OnChanges {
  lines: LineItemDetail[] = [];
  total: number = 0;

  @Input() allocations: EmployeeAllocation[] = [];
  @Input() payslips: IrisPayslip[] = [];
  @Input() payrollDate: string = '';
  @Input() details: PayrollEntryTypeDetails;

  private loadingIndicatorService = inject(LoadingIndicatorService);
  private payrollService = inject(PayrollService);
  private qbPayrollService = inject(QBPayrollService);
  private alertService = inject(AlertService);

  constructor() {
    this.details = new PayrollEntryTypeDetails();
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (!this.payslips.length || !this.allocations.length) return;

    this.total = 0;
    this.lines = [];

    this.details
      .costAllocations(this.payslips, this.allocations)
      .pipe(
        tap((line: LineItemDetail) => this.lines.push(line)),
        scan((a: number, v: LineItemDetail) => a + v.amount, 0),
      )
      .subscribe((total: number) => (this.total = total));
  }

  /** Present the string 'Charity Shop' for the class when the class is '01 Unrestricted'.
   * Can do this because only the shop emnployees have that class allocation.
   */
  className(name_from_quickbooks: string): string {
    return name_from_quickbooks.startsWith('01')
      ? 'Charity Shop'
      : name_from_quickbooks;
  }

  /**
   * Create a single new journal entry in the Charity Quickbooks file that records the Employer NI amounts and
   * account and class allocations for each employee.
   */
  createTransaction() {
    // Filter out lines for which there is already a QBO entry
    const linesToAdd = this.lines.filter((item) => {
      this.payslips.filter(
        (p) =>
          p.payrollNumber == item.payrollNumber &&
          !this.details.inQBOProperty(p),
      ).length;
    });
    if (linesToAdd && linesToAdd.length) {
      this.details
        .transactionCreation(linesToAdd, this.payrollDate)
        .pipe(
          this.loadingIndicatorService.createObserving({
            loading: () =>
              `Adding ${this.details.title} ${this.details.transactionType} to Quickbooks`,
            success: (result) =>
              `Successfully created ${this.details.transactionType} with id=${result.id} in Quickbooks.`,
            error: (err) => `${err}`,
          }),
          shareReplay(1),
        )
        .subscribe();
    } else {
      this.alertService.info(
        'There are no entries to add: they are all in Quickbooks already.',
      );
    }
  }

  /**
   * Check if the values contianed in the given LineItemDetail have been flagged as having already been
   * entered in Quickbooks.
   * @param line The details of the entry
   * @returns 'True' if already in QBO.
   */
  inQBO(line: LineItemDetail): boolean {
    if (!this.payslips || !this.payslips.length) return false;
    return (
      this.payslips.filter(
        (p) =>
          p.payrollNumber == line.payrollNumber &&
          this.details.inQBOProperty(p),
      ).length != 0
    );
  }
}
