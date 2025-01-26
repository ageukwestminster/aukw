import { Component, Input } from '@angular/core';
import { NgFor, NgIf, DecimalPipe } from '@angular/common';
import { IrisPayslip } from '@app/_models';
import { NgbTooltip } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'payslip-list',
  templateUrl: 'list.component.html',
  standalone: true,
  imports: [NgbTooltip, NgFor, NgIf, DecimalPipe],
  styleUrls: ['../shared.css'],
})
export class PayslipListComponent {
  @Input() payslips!: IrisPayslip[];
  @Input() total!: IrisPayslip;
}
