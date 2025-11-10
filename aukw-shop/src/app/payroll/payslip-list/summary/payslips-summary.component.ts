import { Component, inject, Input } from '@angular/core';
import { AsyncPipe, DatePipe, DecimalPipe } from '@angular/common';
import { NgbOffcanvas } from '@ng-bootstrap/ng-bootstrap';
import { Observable, of } from 'rxjs';
import { IrisPayslip } from '@app/_models';
import { PayrollCostByClassComponent } from '../payroll-cost-by-class/payroll-cost-by-class.component';

@Component({
  selector: 'payslips-summary',
  imports: [AsyncPipe, DatePipe, DecimalPipe],
  templateUrl: './payslips-summary.component.html',
  styleUrl: './payslips-summary.component.css',
})
export class PayslipsSummaryComponent {
  @Input() payslips: IrisPayslip[] = [];
  @Input() total: IrisPayslip = new IrisPayslip();
  @Input() topClasses$: Observable<[string, string, number][]> = of([]);

  private offcanvasService = inject(NgbOffcanvas);

  // TODO: open offcanvas for whole list of top classes
  showAllClasses() {
    const offcanvasRef = this.offcanvasService.open(
      PayrollCostByClassComponent,
    );

    // Pass known values to offcanvas component
    this.topClasses$.subscribe(
      (value) => (offcanvasRef.componentInstance.topClasses = value),
    );
  }
}
