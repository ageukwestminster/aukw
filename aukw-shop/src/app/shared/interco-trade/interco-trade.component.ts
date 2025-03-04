import { Component, inject, Input, OnInit, SimpleChanges } from '@angular/core';
import { NgFor, NgIf } from '@angular/common';
import {
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';
import {
  NgbDateAdapter,
  NgbDateParserFormatter,
  NgbDatepickerModule,
} from '@ng-bootstrap/ng-bootstrap';
import { environment } from '@environments/environment';
import { QBAccountListEntry, QBAttachment, ValueIdPair } from '@app/_models';
import {
  AlertService,
  QBAttachmentService,
  QBEntityService,
} from '@app/_services';
import { CustomDateParserFormatter, NgbUTCStringAdapter } from '@app/_helpers';
import { switchMap } from 'rxjs';

@Component({
  selector: 'interco-trade',
  imports: [NgbDatepickerModule, NgFor, NgIf, ReactiveFormsModule],
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
  vendors: ValueIdPair[] = [];
  accounts: ValueIdPair[] = [];

  private realmid: string = environment.qboEnterprisesRealmID;
  private otherRealmid: string = environment.qboCharityRealmID;

  private formBuilder = inject(FormBuilder);
  private attachmentService = inject(QBAttachmentService);
  private entityService = inject(QBEntityService);
  private alertService = inject(AlertService);

  ngOnInit(): void {
    this.form = this.formBuilder.group({
      txnDate: [null],
      entity: [null],
      amount: [
        null,
        [Validators.required, Validators.pattern('^[0-9]+(.[0-9]{0,1})?$')],
      ],
      IsVAT: [false],
      Note: [null],
      attachments: [null],
    });
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['enterprises']) {
      if (this.enterprises) {
        this.realmid = environment.qboEnterprisesRealmID;
        this.otherRealmid = environment.qboCharityRealmID;
      } else {
        this.realmid = environment.qboCharityRealmID;
        this.otherRealmid = environment.qboEnterprisesRealmID;
      }

      // Get a list of vendors and accounts
      this.loading = true;
      this.entityService
        .getAllVendors(this.otherRealmid)
        .pipe(
          switchMap((response) => {
            this.vendors = response;
            return this.entityService.getAllAccounts(this.otherRealmid);
          }),
        )
        .subscribe({
          next: (response) => {
            this.accounts = response;
          },
          error: (error: any) => {
            this.loading = false;
            this.accounts = [];
            this.vendors = [];
            this.alertService.error(error, { autoClose: false });
          },
          complete: () => (this.loading = false),
        });
    }

    if (changes['existingTrade']) {
      if (!this.existingTrade) return;

      // Set values we know already
      this.f['amount'].setValue(this.existingTrade.amount);
      this.f['txnDate'].setValue(this.existingTrade.date);
      this.f['Note'].setValue(this.existingTrade.memo);

      // Try to match Vendor
      if (this.existingTrade.account.value=='Pleo') // PLEO

      // Download attachemnts (if any)
      this.loading = true;
      this.attachments = [];
      this.attachmentService
        .downloadAttachments(
          this.realmid,
          this.existingTrade.type.value,
          this.existingTrade.type.id,
        )
        .subscribe({
          next: (response) => {
            this.attachments = response;
            this.f['attachments'].setValue(this.attachments.length);
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
