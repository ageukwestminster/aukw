import { Component, EventEmitter, Input, Output } from '@angular/core';
import { DecimalPipe } from '@angular/common';
import { IrisPayslip } from '@app/_models';
import { NgbTooltip } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'payslip-list',
  templateUrl: 'list.component.html',
  standalone: true,
  imports: [NgbTooltip, DecimalPipe],
  styleUrls: ['../../shared.css'],
})
export class PayslipListComponent {
  @Input() payslips!: IrisPayslip[];
  @Input() total: IrisPayslip = new IrisPayslip();

  @Output() employeeToAdd: EventEmitter<IrisPayslip> =
    new EventEmitter<IrisPayslip>();

  addEmployee(payslip: IrisPayslip) {
    this.employeeToAdd.emit(payslip);
  }
}
