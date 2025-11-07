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

  /*topNProjects() : Observable<ValueStringIdPair[]> {
    return from(this.payslips).pipe(
      toArray(),
      map (items => {
        var output:ValueStringIdPair[] = [];
        items.forEach(element => {
          if (output.find(x => x.id === element.))
        });
      return output;
      })
    )
  }*/
}
