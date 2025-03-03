import { Component, inject, Input, OnInit, SimpleChanges } from '@angular/core';
import { NgIf } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import {
  NgbDateAdapter,
  NgbDateParserFormatter,
  NgbDatepickerModule,
} from '@ng-bootstrap/ng-bootstrap';
import { environment } from '@environments/environment';
import { QBAccountListEntry, QBAttachment } from '@app/_models';
import { AlertService, QBAttachmentService } from '@app/_services';
import { CustomDateParserFormatter, NgbUTCStringAdapter } from '@app/_helpers';

@Component({
  selector: 'interco-trade',
  imports: [NgbDatepickerModule, NgIf, ReactiveFormsModule],
  templateUrl: './interco-trade.component.html',
  styleUrl: './interco-trade.component.css',
  providers: [
    { provide: NgbDateAdapter, useClass: NgbUTCStringAdapter },
    { provide: NgbDateParserFormatter, useClass: CustomDateParserFormatter },
  ],
})
export class IntercoTradeComponent implements OnInit {
  @Input() existingTrade: QBAccountListEntry | null = null;
  @Input() enterprises: boolean = true; // When 'true' existingTrade is in Enterprises, in Charity otherwise

  form!: FormGroup;
  submitted = false;
  loading = false;
  attachments: QBAttachment[] = [];

  private realmid: string = environment.qboEnterprisesRealmID;

  private formBuilder = inject(FormBuilder);
  private attachmentService = inject(QBAttachmentService);
  private alertService = inject(AlertService);

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
    if (changes['enterprises']) {
      if (this.enterprises) {
        this.realmid = environment.qboEnterprisesRealmID;
      } else {
        this.realmid = environment.qboCharityRealmID;
      }
    }

    if (changes['existingTrade']) {
      if (!this.existingTrade) return;
      this.loading = true;
      this.f['amount'].setValue(this.existingTrade.amount);
      this.f['txnDate'].setValue(this.existingTrade.date);
      this.f['Note'].setValue(this.existingTrade.memo);
      this.attachmentService
        .downloadAttachments(
          this.realmid,
          this.existingTrade.type.value,
          this.existingTrade.type.id,
        )
        .subscribe({
          next: (response) => {
            this.attachments = response;
            var ct = this.attachments.length;
            console.log(this.attachments);
          },
          error: (error: any) => {
            this.loading = false;
            this.attachments = [];
            this.alertService.error(error, { autoClose: false });
          },
          complete: () => (this.loading = false),
        });
    }
  }

  /** Convenience getter for easy access to form fields */
  get f() {
    return this.form.controls;
  }

  vatCheckboxClick() {}
}
