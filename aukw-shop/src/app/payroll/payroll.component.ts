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
import { IrisPayslipsComponent } from './payslips/iris-payslips.component';
import { EmployerNiComponent } from './employerni/employer-ni.component';
import { PayslipListComponent } from './payslips/list/list.component';
import { SharedModule } from '@app/shared/shared.module';

@Component({
  standalone: true,
  imports: [
    EmployeeAllocationsComponent,
    EmployerNiComponent,
    PayslipListComponent,
    NgbNavModule,
    NgIf,
    RouterLink,
    SharedModule,
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

  xlsxWasUploaded(payslips: IrisPayslip[]): void {
    if (!payslips || !payslips.length) return;

    payslips.forEach((payslip) => {
      // Set flag for shop employees according to the allocations array values
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

    this.qbPayrollService.sendPayslips(payslips);

    this.payrollDate = payslips[0].payrollDate;
    const dt = new Date(this.payrollDate + 'T12:00:00');
    this.payrollMonth = (dt.getMonth() + 1).toString().padStart(2, '0');
    this.payrollYear = dt.getFullYear().toString();

    this.qbPayrollService
      .payslipFlagsForCharity(
        this.payslips,
        this.payrollYear,
        this.payrollMonth,
      )
      .pipe(
        this.loadingIndicatorService.createObserving({
          loading: () =>
            ' Loading existing payroll transactions from Quickbooks',
          success: () => `Successfully loaded QBO transactions.`,
          error: (err) => `${err}`,
        }),
        shareReplay(1),
      )
      .subscribe({
        next: (response) => this.qbPayrollService.sendPayslips(response),
        error: (error: any) => {
          this.alertService.error(error, { autoClose: false });
        },
      });
  }

  shopPayslipFlags = this.qbPayrollService
    .payslipFlagsForShop(this.payslips, this.payrollYear, this.payrollMonth)
    .pipe(
      this.loadingIndicatorService.createObserving({
        loading: () =>
          ' Loading existing shop payroll transactions from Quickbooks',
        success: () => `Successfully loaded QBO transactions.`,
        error: (err) => `${err}`,
      }),
      shareReplay(1),
    );
}
