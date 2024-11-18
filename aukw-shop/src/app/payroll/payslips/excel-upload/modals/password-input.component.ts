import { Component, Input } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { NgbModalModule, NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'password-input-modal',
  templateUrl: 'password-input.component.html',
  standalone: true,
  imports: [FormsModule, NgbModalModule],
})
export class PasswordInputModalComponent {
  @Input() fileName!: string;
  passwordValue: string = '';
  constructor(public modal: NgbActiveModal) {}
}
