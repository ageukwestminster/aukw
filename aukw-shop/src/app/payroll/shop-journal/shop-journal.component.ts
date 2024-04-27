import {
  Component,
  EventEmitter,
  inject,
  Input,
  OnChanges,
  Output,
  SimpleChanges,
} from '@angular/core';
import { CommonModule, NgFor, NgIf } from '@angular/common';
import { IrisPayslip } from '@app/_models';
import {
  AlertService,
  LoadingIndicatorService,
  QBEmployeeService,
  QBPayrollService,
} from '@app/_services';
import { forkJoin, map, of, shareReplay } from 'rxjs';
import { environment } from '@environments/environment';
import { NgbTooltip } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'shop-journal',
  standalone: true,
  imports: [CommonModule, NgbTooltip, NgFor, NgIf],
  templateUrl: './shop-journal.component.html',
  styleUrl: './shop-journal.component.css',
})
export class ShopJournalComponent implements OnChanges {
  lines: Array<IrisPayslip> = [];
  total: IrisPayslip = new IrisPayslip();

  @Input() payslips: IrisPayslip[] = [];
  @Input() payrollDate: string = '';
  @Output() onTransactionCreated = new EventEmitter();

  private alertService = inject(AlertService);
  private qbPayrollService = inject(QBPayrollService);
  private loadingIndicatorService = inject(LoadingIndicatorService);
  private qbEmployeeService = inject(QBEmployeeService);

  /**
   * On every change of the input variables, recalculate the allocated employer ni costs.
   * @param changes The inputs that have changed. Not used but retained to match interface.
   * @returns void
   */
  ngOnChanges(changes: SimpleChanges): void {
    if (!this.payslips.length) return;

    this.total = new IrisPayslip();

    forkJoin({
      payslips: of(this.payslips.filter((p) => p.isShopEmployee)),
      employees: this.qbEmployeeService.getAll(
        environment.qboEnterprisesRealmID,
      ),
    })
      .pipe(
        map((x) => {
          let returnArray: Array<IrisPayslip> = [];

          // The quickbooks
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
      //console.log(JSON.stringify(this.lines));
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
          error: (error: any) => {
            this.alertService.error(error, { autoClose: false });
          },
          complete: () => this.onTransactionCreated.emit(),
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
}
