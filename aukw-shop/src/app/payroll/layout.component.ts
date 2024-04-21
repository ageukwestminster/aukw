import { Component, inject, OnInit } from '@angular/core';
import { NgIf } from '@angular/common';
import { RouterLink } from '@angular/router';
import { NgbNavModule } from '@ng-bootstrap/ng-bootstrap';
import { mergeAll, tap } from 'rxjs';
import {
  AlertService,
  AuthenticationService,
  QBRealmService,
} from '@app/_services';
import { EmployeeAllocation, IrisPayslip, QBRealm, User } from '@app/_models';
import { EmployeeAllocationsComponent } from './allocations/employee-allocations.component';
import { IrisPayslipsComponent } from './payslips/iris-payslips.component';
import { SharedModule } from '@app/shared/shared.module';

@Component({
  standalone: true,
  imports: [
    EmployeeAllocationsComponent,
    IrisPayslipsComponent,
    NgbNavModule,
    NgIf,
    RouterLink,
    SharedModule,
  ],
  templateUrl: 'layout.component.html',
})
export class PayrollLayoutComponent {
  user: User;

  active = 1;

  charityRealm!: QBRealm;
  enterpriseRealm!: QBRealm;
  qboAuthorisationMissing: boolean = false;

  allocations: EmployeeAllocation[] = [];

  private authenticationService = inject(AuthenticationService);
  private qbRealmService = inject(QBRealmService);
  private alertService = inject(AlertService);

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
      )
      .subscribe({
        error: (error: any) => {
          this.alertService.error(error);
        },
      });
  }

  receiveAllocations(array: EmployeeAllocation[]) {
    this.allocations = array;
  }
  receivePayslips(array: IrisPayslip[]) {
    
  }
}
