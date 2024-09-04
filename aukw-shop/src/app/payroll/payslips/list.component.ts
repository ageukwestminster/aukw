import { Component, Input } from '@angular/core';
import { CommonModule, NgFor, NgIf } from '@angular/common';
import { IrisPayslip } from '@app/_models';
import { NgbTooltip } from '@ng-bootstrap/ng-bootstrap';
import { Observable, mergeAll, scan } from 'rxjs';

@Component({
  selector: 'payslip-list',
  templateUrl: 'list.component.html',
  standalone: true,
  imports: [CommonModule, NgbTooltip, NgFor, NgIf],
  styleUrls: ['list.component.css', '../shared.css'],
})
export class PayslipListComponent {
  private _payslips!: IrisPayslip[];
  public total!: IrisPayslip;

  @Input() set payslips(payslips: IrisPayslip[]) {

    // loop through all payslips and sum the values 
    // to form a new "total" payslip and put in class level variable
    this.total = new IrisPayslip();
    payslips.forEach((payslip) => {
      this.total = this.total.add(payslip);
    });

    this._payslips = payslips;
  }

  get payslips(): IrisPayslip[] {
    return this._payslips;
  }
/*
  totalPayslips(payslips$: Observable<IrisPayslip[]>): Observable<IrisPayslip> {
    return payslips$.pipe(
      mergeAll(),
      scan((a: IrisPayslip, v: IrisPayslip) => {
        return a.add(v);
      }, new IrisPayslip()),
    );
  }*/
}
