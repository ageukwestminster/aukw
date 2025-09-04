import { Component, inject } from '@angular/core';
import { Location, NgClass } from '@angular/common';
import { RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';
import { NgbNavModule } from '@ng-bootstrap/ng-bootstrap';
import { BehaviorSubject } from 'rxjs';
import { PayrollProcessStateService } from '@app/_services';
import { PayrollProcessState } from '@app/_models';

@Component({
  templateUrl: 'frontpage.component.html',
  standalone: true,
  imports: [NgbNavModule, RouterLink, RouterLinkActive, RouterOutlet],
  styleUrl: './frontpage.component.css',
})
export class PayrollFrontPageComponent {
  state$: BehaviorSubject<PayrollProcessState>;

  private payrollProcessStateService = inject(PayrollProcessStateService);
  private location = inject(Location);

  constructor() {
    this.state$ = this.payrollProcessStateService.stateSubject;
  }

  get processState(): PayrollProcessState {
    return this.state$.getValue();
  }
  get processStateValue(): string {
    return PayrollProcessState[this.state$.getValue()];
  }
  get locationString(): string {
    return window.location.pathname.toUpperCase().split('/').pop()!;
  }

  // line added to expose enum to template
  PayrollProcessState = PayrollProcessState;

  get math() {
    return Math;
  }
}
