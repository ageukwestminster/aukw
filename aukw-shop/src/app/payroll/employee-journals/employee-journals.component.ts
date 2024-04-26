import {
  Component,
  EventEmitter,
  inject,
  Input,
  OnChanges,
  Output,
  SimpleChanges,
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { EmployeeAllocation, IrisPayslip, PayrollJournalEntry } from '@app/_models';
import {
  AlertService,
  LoadingIndicatorService,
  QBPayrollService,
  PayrollService,
} from '@app/_services';
import { scan, shareReplay, tap, toArray } from 'rxjs';

@Component({
  selector: 'employee-journals',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './employee-journals.component.html',
  styleUrl: './employee-journals.component.css'
})
export class EmployeeJournalsComponent implements OnChanges {
  lines: PayrollJournalEntry[] = [];

  @Input() allocations: EmployeeAllocation[] = [];
  @Input() payslips: IrisPayslip[] = [];
  @Input() payrollDate: string = '';
  @Output() onTransactionCreated = new EventEmitter();

  private loadingIndicatorService = inject(LoadingIndicatorService);
  private payrollService = inject(PayrollService);
  private qbPayrollService = inject(QBPayrollService);
  private alertService = inject(AlertService);

    /**
   * On every change of the input variables, recalculate the allocated employer ni costs.
   * @param changes The inputs that have changed. Not used but retained to match interface.
   * @returns void
   */
    ngOnChanges(changes: SimpleChanges): void {
      if (!this.payslips.length || !this.allocations.length) return;

      this.payrollService
      .employeeJournalEntries(this.payslips, this.allocations)
      .pipe(
        toArray(),
      )
      .subscribe({
        next: (response) => this.lines = response,
        error: (e) => {
          this.alertService.error(e, { autoClose: false });
        },
      });
    }
}
