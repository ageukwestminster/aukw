import {
  Component,
  inject,
  Input,
  OnChanges,
  SimpleChanges,
} from '@angular/core';
import { CommonModule, NgFor, NgIf } from '@angular/common';
import {
  EmployeeAllocation,
  IrisPayslip,
  LineItemDetail,
  QBRealm,
} from '@app/_models';
import {
  LoadingIndicatorService,
  PayrollService,
  QBPayrollService,
} from '@app/_services';
import { shareReplay, scan, tap } from 'rxjs';

@Component({
  selector: 'employer-ni',
  standalone: true,
  imports: [CommonModule, NgFor, NgIf],
  templateUrl: './employer-ni.component.html',
  styleUrl: './employer-ni.component.css',
})
export class EmployerNiComponent implements OnChanges {
  lines: LineItemDetail[] = [];
  total: number = 0;

  @Input() charityRealm!: QBRealm;
  @Input() enterpriseRealm!: QBRealm;
  @Input() allocations: EmployeeAllocation[] = [];
  @Input() payslips: IrisPayslip[] = [];

  private loadingIndicatorService = inject(LoadingIndicatorService);
  private payrollService = inject(PayrollService);
  private qbPayrollService = inject(QBPayrollService);

  constructor() {}

  ngOnChanges(changes: SimpleChanges):void {
    if (!this.payslips.length || !this.allocations.length) return;

    this.total = 0;
    this.lines = [];

    this.payrollService
      .employerNIAllocatedCosts(
        this.payslips.filter((payslip) => !payslip.niJournalInQBO),
        this.allocations,
      )
      .pipe(
        //toArray(), //concat them into an array
        tap((line: LineItemDetail) => this.lines.push(line)),
        scan((a: number, v: LineItemDetail) => a + v.amount, 0),
      )
      .subscribe((total: number) => (this.total = total));
  }

  className(name_from_quickbooks: string): string {
    return name_from_quickbooks.startsWith('01')
      ? 'Charity Shop'
      : name_from_quickbooks;
  }

  createEmployerNIJournal() {
    this.qbPayrollService.createEmployerNIJournal(
      this.lines,
      this.payslips[0].payrollDate,
    ).pipe(
      this.loadingIndicatorService.createObserving({
        loading: () => 'Adding employer NI journal to Quickbooks',
        success: (result) =>
          `Successfully created journal with id=${result.id} in Quickbooks.`,
        error: (err) => `${err}`,
      }),
      shareReplay(1),
    ).subscribe({
    next: () => {/*this.payrollService.updateempNI*/ }
    });
  }
}
