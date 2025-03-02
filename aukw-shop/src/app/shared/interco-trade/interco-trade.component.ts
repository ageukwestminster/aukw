import { Component, inject, Input, OnInit, SimpleChanges } from '@angular/core';
import { NgClass, NgIf } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import {
  NgbDateAdapter,
  NgbDateParserFormatter,
  NgbDatepickerModule,
} from '@ng-bootstrap/ng-bootstrap';

import { QBAccountListEntry } from '@app/_models';
import { CustomDateParserFormatter, NgbUTCStringAdapter } from '@app/_helpers';

@Component({
  selector: 'interco-trade',
  imports: [NgbDatepickerModule, NgClass, NgIf, ReactiveFormsModule],
  templateUrl: './interco-trade.component.html',
  styleUrl: './interco-trade.component.css',
  providers: [
    { provide: NgbDateAdapter, useClass: NgbUTCStringAdapter },
    { provide: NgbDateParserFormatter, useClass: CustomDateParserFormatter },
  ],
})
export class IntercoTradeComponent implements OnInit {
  @Input() existingTrade: QBAccountListEntry | null = null;
  @Input() enterprises: boolean = true; // When 'true' use Enterprises company, Charity otherwise

  form!: FormGroup;
  submitted = false;
  loading = false;

  private formBuilder = inject(FormBuilder);

  ngOnInit(): void {
    this.form = this.formBuilder.group({
      txnDate: [null],
      entity: [null],
      amount: [null],
      IsVAT: [false],
      Note: [null],
    });
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (!this.existingTrade) return;

    console.log(this.existingTrade);
  }

  /** Convenience getter for easy access to form fields */
  get f() {
    return this.form.controls;
  }
}
