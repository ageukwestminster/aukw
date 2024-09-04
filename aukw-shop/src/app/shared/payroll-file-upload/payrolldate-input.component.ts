import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { NgbModalModule, 
  NgbActiveModal,
  NgbDateAdapter,
  NgbDateParserFormatter,
  NgbDatepickerModule
 } from '@ng-bootstrap/ng-bootstrap';
 import { CustomDateParserFormatter, NgbUTCStringAdapter } from '@app/_helpers';

@Component({
  selector: 'payrolldate-input-modal',
  templateUrl: 'payrolldate-input.component.html',
  standalone: true,
  imports: [FormsModule, NgbModalModule, NgbDatepickerModule],
  providers: [
    { provide: NgbDateAdapter, useClass: NgbUTCStringAdapter },
    { provide: NgbDateParserFormatter, useClass: CustomDateParserFormatter },
  ]
})
export class PayrollDateInputModalComponent {
  payrollDate: string = '';
  constructor(public modal: NgbActiveModal) {}
}
