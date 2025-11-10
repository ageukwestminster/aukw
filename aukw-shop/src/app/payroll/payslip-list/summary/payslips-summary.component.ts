import { Component, Input } from '@angular/core';
import { DatePipe, DecimalPipe } from '@angular/common';
import { from, map, Observable, toArray } from 'rxjs';
import { IrisPayslip, ValueStringIdPair } from '@app/_models';

@Component({
  selector: 'payslips-summary',
  imports: [DatePipe, DecimalPipe],
  templateUrl: './payslips-summary.component.html',
  styleUrl: './payslips-summary.component.css',
})
export class PayslipsSummaryComponent {
  @Input() payslips: IrisPayslip[] = [];
  @Input() total: IrisPayslip = new IrisPayslip();
  @Input() topClasses: [string, string, number][] = [];
}
