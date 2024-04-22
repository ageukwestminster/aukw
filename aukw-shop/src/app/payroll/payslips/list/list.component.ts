import { Component, Input } from '@angular/core';
import { NgFor, NgIf } from '@angular/common';
import { Observable, of, scan, switchMap, merge } from 'rxjs';
import { IrisPayslip } from '@app/_models';

@Component({
  selector: 'payslip-list',
  templateUrl: 'list.component.html',
  standalone: true,
  imports: [NgFor, NgIf],
  styleUrls: ['list.component.css'],
})
export class PayslipListComponent2 {
  _payslips$?: Observable<IrisPayslip[]>;
  _total$?: Observable<IrisPayslip>;
  @Input() set payslips(value: Observable<IrisPayslip[]>) {
    this._payslips$ = value;

    this._total$ = value.pipe(
      switchMap((dataArray: IrisPayslip[]) => {
        const obs = dataArray.map((x) => {
          return of(x);
        });
        return merge(...obs);
      }),
      scan(
        (acc: IrisPayslip, value: IrisPayslip) => acc.add(value),
        new IrisPayslip(),
      ),
    );   
  }
}
