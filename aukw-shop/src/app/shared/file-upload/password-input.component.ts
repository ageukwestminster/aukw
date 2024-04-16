import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'password-input-modal',
  templateUrl: 'password-input.component.html',
})
export class PasswordInputModalComponent {
  passwordValue: string ='';
  constructor(public modal: NgbActiveModal) {}
}
