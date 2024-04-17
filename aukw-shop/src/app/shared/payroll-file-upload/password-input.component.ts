import { Component, Input } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'password-input-modal',
  templateUrl: 'password-input.component.html',
})
export class PasswordInputModalComponent {
  @Input() fileName!: string;
  passwordValue: string = '';
  constructor(public modal: NgbActiveModal) {}
}
