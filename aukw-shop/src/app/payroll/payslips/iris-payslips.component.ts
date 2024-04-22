import { Component, EventEmitter, inject, Input, Output } from '@angular/core';
import { SharedModule } from '@app/shared/shared.module';

import { EmployeeAllocation, IrisPayslip, QBRealm } from '@app/_models';
import { PayslipListComponent } from './list/list.component';
import { isEqualPay, isEqualPension, isEqualEmployerNI, isEqualShopPay  } from '@app/_helpers';
import { LoadingIndicatorService, QBPayrollService } from '@app/_services';
import { forkJoin, map, shareReplay} from 'rxjs';

@Component({
  selector: 'iris-payslips',
  standalone: true,
  imports: [PayslipListComponent, SharedModule],
  templateUrl: './iris-payslips.component.html',
  styleUrl: './iris-payslips.component.css',
})
export class IrisPayslipsComponent {
  _payslips: IrisPayslip[] = [];
  _allocations: EmployeeAllocation[] = [];

  /**
   * When the payslips have been loaded we will emit them
   */
  @Output() onPayslipsExtractedFromSpreadsheet = new EventEmitter<
    IrisPayslip[]
  >();

  @Input() charityRealm! : QBRealm;
  @Input() enterpriseRealm! : QBRealm;
  @Input() allocations : EmployeeAllocation[] = [];

  private qbPayrollService = inject(QBPayrollService);
  private loadingIndicatorService = inject(LoadingIndicatorService);

  constructor() {}

  xlsxWasUploaded(payslips: IrisPayslip[]): void {
    payslips.forEach((payslip) => {
      // Set flag for shop employees according to the allocations array values
      if (
        this.allocations.find(
          (item) =>
            item.isShopEmployee && item.payrollNumber == payslip.payrollNumber,
        )
      ) {
        payslip.isShopEmployee = true;
      }
    });
    this._payslips = payslips;

    this.updateInQBOValues();

    this.onPayslipsExtractedFromSpreadsheet.emit(this._payslips);
  }

  updateInQBOValues() {
    const dt = new Date(this._payslips[0].payrollDate + 'T12:00:00');
    const year = dt.getFullYear().toString();
    const month = (dt.getMonth() + 1).toString().padStart(2, '0');

    forkJoin({
      charityPayslips: this.qbPayrollService.getWhatsAlreadyInQBO(
        this.charityRealm.realmid!,
        year,
        month,
      ),
      shopPayslips: this.qbPayrollService.getWhatsAlreadyInQBO(
        this.enterpriseRealm.realmid!,
        year,
        month,
      ),
    })
    .pipe(
      map((x) => {
        this._payslips.forEach((xlsxPayslip) => {
          let qbPayslip = x.charityPayslips.find(
            (item) => item.payrollNumber == xlsxPayslip.payrollNumber,
          );
          qbPayslip = qbPayslip ?? new IrisPayslip();

          xlsxPayslip.niJournalInQBO = isEqualEmployerNI(
            xlsxPayslip,
            qbPayslip,
          );
          xlsxPayslip.pensionBillInQBO = isEqualPension(
            xlsxPayslip,
            qbPayslip,
          );
          xlsxPayslip.payslipJournalInQBO = isEqualPay(
            xlsxPayslip,
            qbPayslip,
          );

          const qbShopPayslip = x.shopPayslips.find(
            (item) => item.payrollNumber == xlsxPayslip.payrollNumber,
          );

          xlsxPayslip.shopJournalInQBO = isEqualShopPay(
            xlsxPayslip,
            qbShopPayslip ?? new IrisPayslip(),
          );
        });
      }),
      this.loadingIndicatorService.createObserving({
        loading: () => 'Loading payroll transactions from Quickbooks',
        success: () =>
          `Successfully loaded transactions from Quickbooks`,
        error: (err) => `${err}`,
      }),
      shareReplay(1),
    )
    .subscribe();
  }
}
