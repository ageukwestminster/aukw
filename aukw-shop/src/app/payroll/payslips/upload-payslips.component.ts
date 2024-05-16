import { Component, DestroyRef, inject, OnInit } from '@angular/core';
import { CommonModule, NgFor, NgIf } from '@angular/common';
import { EmployeeAllocation, IrisPayslip, PayrollProcessState } from '@app/_models';
import { NgbTooltip } from '@ng-bootstrap/ng-bootstrap';
import { concatMap, distinctUntilChanged, forkJoin, shareReplay, Subject, switchMap, takeUntil, tap } from 'rxjs';
import { PayslipListComponent } from './list.component';
import { PayrollFileUploadComponent } from '@app/shared';
import { AlertService, LoadingIndicatorService, PayrollProcessStateService, QBPayrollService } from '@app/_services';

@Component({
  templateUrl: 'upload-payslips.component.html',
  standalone: true,
  imports: [ NgIf, PayslipListComponent, PayrollFileUploadComponent],
})
export class UploadPayslipsComponent implements OnInit { 

  allocations: EmployeeAllocation[] = [];
  payslips: IrisPayslip[] = [];
  payrollDate: string = '';

  private alertService = inject(AlertService);
  private qbPayrollService = inject(QBPayrollService);
  private loadingIndicatorService = inject(LoadingIndicatorService);
  private stateService = inject (PayrollProcessStateService);
  private destroyRef = inject(DestroyRef);

  PayrollProcessState = PayrollProcessState;

  /**
   * This pattern is used to subscribe to an rxjs Subject and automatically
   * unsubscribe when the object is destroyed. Angular gives us the destroyRef
   * hook to manage this.
   * { @link https://medium.com/@chandrashekharsingh25/exploring-the-takeuntildestroyed-operator-in-angular-d7244c24a43e }
   */
  ngOnInit() {
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
   *  the API has uploaded and decrypted the file and converted it into
   *  an array of IrisPayslip.
   */
  xlsxWasUploaded(payslips: IrisPayslip[]): void {
    if (!payslips || !payslips.length) return;


    if (!this.allocations || ! this.allocations.length) {
      this.alertService.error(
        'Something has gone wrong. There are no employee project allocations loaded. '+
        'Please return to the Allocations page to reload the employee project allocations before proceeding.'
        , { autoClose: false }
      );
      return;
    }

    try {
      payslips.forEach((payslip) => {
        const allocation = this.allocations.find(
          (item) => item.payrollNumber == payslip.payrollNumber,
        );

        if (!allocation) {
          throw new Error(
            'The recurring transaction in Quickbooks that ' +
              `defines the class allocations does not have an entry for '${payslip.employeeName}'.` +
              `Please add them to the salary allocations recurring transaction and then try again.`,
          );
        }

        // Set flag for shop employees using the allocations array
        if (allocation.isShopEmployee) {
          payslip.isShopEmployee = true;
        } else {
          payslip.isShopEmployee = false;
        }
      });
    } catch (error) {
      //Code from https://kentcdodds.com/blog/get-a-catch-block-error-message-with-typescript
      let message: string;
      if (error instanceof Error) {
        message = error.message;
      } else {
        message = String(error);
      }
      this.alertService.error(message, { autoClose: false });
      return; // Return and do not proceed with processing payslips
    }

    // Update the payslips subject so it will be availabe to all subscribers
    this.qbPayrollService.sendPayslips(payslips);

    this.updateQBOFlags(payslips, payslips[0].payrollDate);
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
  updateQBOFlags(payslips: IrisPayslip[], payrollDate: string) {

    this.qbPayrollService
      .payslipFlagsForCharity(
        payslips,
        payrollDate,
      )
      .pipe(
        concatMap((response) => {
          return this.qbPayrollService.payslipFlagsForShop(
            response,
            payrollDate
          );
        }),
        this.loadingIndicatorService.createObserving({
          loading: () =>
            ' Querying Quickbooks to see if transactions already entered.',
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
        complete: () => {
          this.stateService.setState(PayrollProcessState.PAYSLIPS)
        },
      });
  }
}