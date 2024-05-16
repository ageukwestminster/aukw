import { Component, inject, TemplateRef } from '@angular/core';
import { AsyncPipe, NgIf } from '@angular/common';
import { RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';
import { NgbNavModule } from '@ng-bootstrap/ng-bootstrap';
import { BehaviorSubject } from 'rxjs';
import { PayrollProcessStateService } from '@app/_services';
import { PayrollProcessState } from '@app/_models';

@Component({
  templateUrl: 'frontpage.component.html',
  standalone: true,
  imports: [
    AsyncPipe,
    NgbNavModule,
    NgIf,
    RouterLink,
    RouterLinkActive,
    RouterOutlet,
  ],
  styleUrl: './frontpage.component.css',
})
export class PayrollFrontPageComponent {
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
