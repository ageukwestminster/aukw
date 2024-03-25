import { Component} from '@angular/core';
import { FileService } from '@app/_services';
import { IrisPayslip } from '@app/_models';

@Component({ templateUrl: 'list.component.html' })
export class PayslipListComponent {
  payslips:IrisPayslip[] = [];
  total:IrisPayslip = new IrisPayslip();
    
  constructor(private fileService: FileService) {}

  xlsxWasUploaded(payslips:IrisPayslip[]): void {
    this.payslips = payslips;

    this.total = new IrisPayslip();
    payslips.forEach(element => {
      this.total = this.total.add(element);
    });
  }


  /**
   * Convenience getter to expose value to template
   */
  get payrollDate():string {
    return (this.payslips && this.payslips[0])?
                      this.payslips[0].payrollDate:'';
  }

  updateQBO() {
    if (this.payslips && this.payslips[0]) {
    this.payslips[0].inQBO = true; }
  }

  employerNI() {
    
  }
}
