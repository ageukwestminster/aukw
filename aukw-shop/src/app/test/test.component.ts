import { Component, inject, TemplateRef } from '@angular/core';
import { AsyncPipe, NgIf, NgClass } from '@angular/common';
import { RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';
import { NgbNavModule } from '@ng-bootstrap/ng-bootstrap';
import { BehaviorSubject } from 'rxjs';
import { PayrollProcessStateService } from '@app/_services';
import { PayrollProcessState } from '@app/_models';

@Component({
  selector: 'app-test',
  standalone: true,
  imports: [
    AsyncPipe,
    NgbNavModule,
    NgClass,
    NgIf,
    RouterLink,
    RouterLinkActive,
    RouterOutlet,
  ],
  templateUrl: './test.component.html',
  styleUrl: './test.component.css',
})
export class TestComponent {
  state$: BehaviorSubject<PayrollProcessState>;
  private payrollProcessStateService = inject(PayrollProcessStateService);
  constructor() {
    this.state$ = this.payrollProcessStateService.stateSubject;
  }
  PayrollProcessState = PayrollProcessState;
  get processState(): PayrollProcessState {
    return this.state$.getValue();
  }
}
