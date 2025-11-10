import { Component, inject, OnInit } from '@angular/core';
import { DecimalPipe } from '@angular/common';
import { NgbActiveOffcanvas } from '@ng-bootstrap/ng-bootstrap';
import { TCEByClassChartComponent } from '@app/shared';

@Component({
  selector: 'app-payroll-cost-by-class',
  imports: [DecimalPipe, TCEByClassChartComponent],
  templateUrl: './payroll-cost-by-class.component.html',
  styleUrl: './payroll-cost-by-class.component.css'
})
export class PayrollCostByClassComponent implements OnInit {
  topClasses: [string, string, number, number][] = [];
  totals: [number, number] = [0,0];

  activeOffcanvas = inject(NgbActiveOffcanvas);

  ngOnInit(){
        var sum: number = 0;
        this.topClasses.forEach(element => {
          sum+=element[2];
        });
        this.totals[0] = sum;
        this.topClasses.forEach(element => {
          const percentage = (100*element[2])/sum
          element[3] = percentage;
          this.totals[1] = this.totals[1]+percentage;
        });
  }
}
