import { Component, EventEmitter, Output } from '@angular/core';
import { SharedModule } from '@app/shared/shared.module';

import { IrisPayslip } from '@app/_models';

@Component({
  selector: 'iris-payslips',
  standalone: true,
  imports: [SharedModule],
  templateUrl: './iris-payslips.component.html',
  styleUrl: './iris-payslips.component.css',
})
export class IrisPayslipsComponent {
  _payslips: IrisPayslip[] = [];
  /**
   * When the payslips have been loaded we will emit them
   */
  @Output() onPayslipsExtractedFromSpreadsheet = new EventEmitter<IrisPayslip[]>();

  xlsxWasUploaded(payslips: IrisPayslip[]): void {
    this._payslips = payslips;
    this.onPayslipsExtractedFromSpreadsheet.emit(payslips);
  }
}
