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
import {
  QBAccountListEntry,
  QBAttachment,
  QBPurchase,
  ValueIdPair,
  ValueIdType,
} from '@app/_models';
import {
  AlertService,
  QBAttachmentService,
  QBEntityService,
  QBPurchaseService,
} from '@app/_services';
import { CustomDateParserFormatter, NgbUTCStringAdapter } from '@app/_helpers';
import { forkJoin } from 'rxjs';

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
  accounts: ValueIdType[] = [];
  customers: ValueIdPair[] = [];
  newTrade!: QBPurchase;

  private realmid: string = environment.qboEnterprisesRealmID;
  private otherRealmid: string = environment.qboCharityRealmID;

  private formBuilder = inject(FormBuilder);
  private attachmentService = inject(QBAttachmentService);
  private entityService = inject(QBEntityService);
  private alertService = inject(AlertService);
  private purchaseService = inject(QBPurchaseService);

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
      account: [null],
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

      this.getVendorsCustomersAccounts(this.otherRealmid);
    }

    if (changes['existingTrade']) {
      if (!this.existingTrade) return;

      switch (this.existingTrade.type.value) {
        case 'Bill':
        case 'Expense':
          this.newTrade = new QBPurchase({
            amount: this.existingTrade.amount,
            txnDate: this.existingTrade.date,
            privateNote: this.existingTrade.memo,
          });
          if (this.existingTrade.account.id == 429) {
            // Pleo
            //extract name from description
            var entityName = this.existingTrade.memo.split('|')[1].trim();

            //find entity in Vendors
            var filterEntities = this.vendors.filter(
              (x) =>
                x.value.toLowerCase().substring(0, 4) ==
                entityName.toLowerCase().substring(0, 4),
            );
            var filteredEntity = filterEntities[0];
            var entity: [number, string] = [
              filteredEntity.id,
              filteredEntity.value,
            ];
            this.newTrade.entity = entity;
            this.f['entity'].setValue(filteredEntity.id);

            //Choose account
            switch (filteredEntity.value.substring(0, 4).toLowerCase()) {
              case 'morp':
                break;

              default:
                break;
            }
          }
          //this.newTrade = this.purchaseService.createNew();
          break;
        case 'Transfer':
          break;
        default:
          break;
      }

      // Set values we know already
      this.f['amount'].setValue(this.existingTrade.amount);
      this.f['txnDate'].setValue(this.existingTrade.date);
      this.f['Note'].setValue(this.existingTrade.memo);

      // Download attachemnts (if any)
      this.downloadAttachments(this.realmid, this.existingTrade);
    }
  }

  /**
   * Downlaod all the attachmnents (if any) for a QBO trade.
   * @param realmid The QBO id of the company file.
   * @param trade The currently selected trade that is already in QBO.
   */
  private downloadAttachments(realmid: string, trade: QBAccountListEntry) {
    this.loading = true;
    this.attachments = [];
    this.attachmentService
      .downloadAttachments(realmid, trade.type.value, trade.type.id)
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

  /**
   * Store vendors, customers and accounts at module-level.
   * @param realmid The QBO id of the company file.
   */
  private getVendorsCustomersAccounts(realmid: string) {
    this.loading = true;

    var $obs = {
      accounts: this.entityService.getAllAccounts(realmid),
      customers: this.entityService.getAllCustomers(realmid),
      vendors: this.entityService.getAllVendors(realmid),
    };

    forkJoin($obs).subscribe({
      next: (x) => {
        var filteredAccounts = x.accounts.filter((x) => {
          //console.log(x.type);

          return (
            x.type.includes('Expense', 0) || x.type == 'Cost of Goods Sold'
          );
        });
        this.accounts = filteredAccounts;
        this.customers = x.customers;
        this.vendors = x.vendors;
      },
      error: (error: any) => {
        this.loading = false;
        this.accounts = [];
        this.customers = [];
        this.vendors = [];
        this.alertService.error(error, { autoClose: false });
      },
      complete: () => (this.loading = false),
    });
  }

  /** Convenience getter for easy access to form fields */
  get f() {
    return this.form.controls;
  }

  vatCheckboxClick() {}
}
