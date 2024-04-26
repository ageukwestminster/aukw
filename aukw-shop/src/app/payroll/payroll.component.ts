import { Component, DestroyRef, inject, OnInit } from '@angular/core';
import { NgIf } from '@angular/common';
import { RouterLink } from '@angular/router';
import { NgbNavModule } from '@ng-bootstrap/ng-bootstrap';
import {
  concatMap,
  toArray,
  mergeAll,
  tap,
  retry,
  shareReplay,
  Subject,
  takeUntil,
} from 'rxjs';
import {
  AlertService,
  AuthenticationService,
  LoadingIndicatorService,
  QBPayrollService,
  QBRealmService,
} from '@app/_services';
import { EmployeeAllocation, IrisPayslip, QBRealm, User } from '@app/_models';
import { EmployeeAllocationsComponent } from './allocations/employee-allocations.component';
import { EmployerNiComponent } from './employerni/employer-ni.component';
import { PayslipListComponent } from './payslips/list.component';
import { SharedModule } from '@app/shared/shared.module';
import { PensionInvoiceComponent } from './pension-invoice/pension-invoice.component';
import { ShopJournalComponent } from './shop-journal/shop-journal.component';
import { EmployeeJournalsComponent } from './employee-journals/employee-journals.component';

@Component({
  standalone: true,
  imports: [
    EmployeeAllocationsComponent,
    EmployeeJournalsComponent,
    EmployerNiComponent,
    PayslipListComponent,
    NgbNavModule,
    NgIf,
    PensionInvoiceComponent,
    RouterLink,
    SharedModule,
    ShopJournalComponent,
  ],
  templateUrl: 'payroll.component.html',
})
export class PayrollComponent implements OnInit {
  user: User;

  active = 1;

  charityRealm!: QBRealm;
  enterpriseRealm!: QBRealm;
  qboAuthorisationMissing: boolean = false;

  payslips: IrisPayslip[] = [];
  allocations: EmployeeAllocation[] = [];
  payrollDate: string = '';
  payrollYear: string = '';
  payrollMonth: string = '';

  loadingComplete: boolean = false;

  private authenticationService = inject(AuthenticationService);
  private qbRealmService = inject(QBRealmService);
  private alertService = inject(AlertService);
  private qbPayrollService = inject(QBPayrollService);
  private loadingIndicatorService = inject(LoadingIndicatorService);
  private destroyRef = inject(DestroyRef);

  constructor() {
    this.user = this.authenticationService.userValue;
  }

  /**
   * Initialize the component by populating the 2 realm properties and
   * the boolean flag that warns if a QBO connection is absent.
   * Also call getAllocations to retrieve percentage allocations for each
   * employee.
   */
  ngOnInit() {
    this.qbRealmService
      .getAll(this.user.id)
      .pipe(
        mergeAll(),
        tap((r: QBRealm) => {
          if (!r.connection || !r.connection.refreshtoken) {
            this.qboAuthorisationMissing = true;
          }

          if (!r.issandbox && r.name) {
            if (/enterprises/i.test(r.name)) {
              this.enterpriseRealm = r;
            } else {
              this.charityRealm = r;
            }
          }
        }),
        toArray(),
        concatMap(() => this.qbPayrollService.getAllocations()),
        retry(2),
        this.loadingIndicatorService.createObserving({
          loading: () => 'Loading employee allocations from Quickbooks',
          success: (result) =>
            `Successfully loaded ${result.length} employee allocations`,
          error: (err) => `${err}`,
        }),
        shareReplay(1),
      )
      .subscribe({
        error: (error: any) => {
          this.alertService.error(error, { autoClose: false });
        },
      });

    this.subscribeToSubjects();
  }

  /**
   * This pattern is used to subscribe to an rxjs Subject and automatically
   * unsubscribe when the object is destroyed. Angular gives us the destroyRef
   * hook to manage this.
   * { @link https://medium.com/@chandrashekharsingh25/exploring-the-takeuntildestroyed-operator-in-angular-d7244c24a43e }
   */
  private subscribeToSubjects(): void {
    const destroyed = new Subject();
    this.destroyRef.onDestroy(() => {
      destroyed.next('');
      destroyed.complete();
    });

    this.qbPayrollService.allocations$
      .pipe(takeUntil(destroyed))
      .subscribe((response) => (this.allocations = response));
    this.qbPayrollService.payslips$
      .pipe(takeUntil(destroyed))
      .subscribe((response) => (this.payslips = response));
  }

  /** This is a callback from the file upload component. It is called when
   *  the API has uploaded and decrypoted the file and converted it into
   *  an array of IrisPayslip.
   */
  xlsxWasUploaded(payslips: IrisPayslip[]): void {
    if (!payslips || !payslips.length) return;

    payslips.forEach((payslip) => {
      // Set flag for shop employees using the allocations array
      if (
        this.allocations.find(
          (item) =>
            item.isShopEmployee && item.payrollNumber == payslip.payrollNumber,
        )
      ) {
        payslip.isShopEmployee = true;
      } else {
        payslip.isShopEmployee = false;
      }
    });

    // Update the payslips subject so it will be availabe to all subscribers
    this.qbPayrollService.sendPayslips(payslips);

    // Calculate month and year from payroll date
    this.payrollDate = payslips[0].payrollDate;
    const dt = new Date(this.payrollDate + 'T12:00:00');
    this.payrollMonth = (dt.getMonth() + 1).toString().padStart(2, '0');
    this.payrollYear = dt.getFullYear().toString();

    this.updateQBOFlags();
  }

  /**
   * Set the 'in Quickbooks' flags for the array of payslips that is held in the
   * instance variable 'payslips'. There are 4 flags:
   *  i) Is the employer NI amount entered in QB?
   *  ii) Are the employee salary and deductions entered in QB?
   *  iii) Is the employer pension amount entered in QB?
   *  iv) Are the numbers for shop employees entered in the Enterprises QB?
   * The function takes the given payslips, sets or unsets the boolean flags for each payslip
   * and then sends the amended array of payslips back to the service for onward broadcast.
   */
  updateQBOFlags() {
    this.qbPayrollService
      .payslipFlagsForCharity(
        this.payslips,
        this.payrollYear,
        this.payrollMonth,
      )
      .pipe(
        concatMap((response) => {
          return this.qbPayrollService.payslipFlagsForShop(
            response,
            this.payrollYear,
            this.payrollMonth,
          );
        }),
        this.loadingIndicatorService.createObserving({
          loading: () =>
            ' Querying Quickbooks for existing payroll transactions.',
          success: () => `Successfully loaded Quickbooks transactions.`,
          error: (err) => `${err}`,
        }),
        shareReplay(1),
      )
      .subscribe({
        next: (response) => this.qbPayrollService.sendPayslips(response),
        error: (error: any) => {
          this.alertService.error(error, { autoClose: false });
        },
        complete: () => (this.loadingComplete = true),
      });
  }
}
