import { Component, inject, TemplateRef } from '@angular/core';
import { AsyncPipe, NgIf, NgClass } from '@angular/common';
import { RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';
import { NgbNavModule } from '@ng-bootstrap/ng-bootstrap';
import { BehaviorSubject } from 'rxjs';
import { PayrollProcessStateService } from '@app/_services';
import { PayrollProcessState } from '@app/_models';

@Component({
  templateUrl: 'frontpage.component.html',
  standalone: true,
  imports: [
    NgbNavModule,
    NgClass,
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
  
  get processState(): PayrollProcessState {
    return this.state$.getValue();
  }

  // line added to expose enum to template
  PayrollProcessState = PayrollProcessState;

  get math() {
    return Math;
  }
}
