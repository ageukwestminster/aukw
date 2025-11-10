import { Component, inject, OnInit } from '@angular/core';
import {
  AbstractControlOptions,
  FormArray,
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';
import { Observable, switchMap } from 'rxjs';
import { environment } from '@environments/environment';
import {
  AllocationsService,
  QBEmployeeService,
  QBEntityService,
  QBPayrollService,
} from '@app/_services';
import {
  EmployeeAllocation,
  EmployeeName,
  ValueStringIdPair,
  ApiMessage,
} from '@app/_models';

@Component({
  selector: 'app-allocations',
  imports: [],
  templateUrl: './allocations.component.html',
  styleUrl: './allocations.component.css',
})
export class AllocationsComponent implements OnInit {
  form!: FormGroup;
  classes: ValueStringIdPair[] = [];
  employees: EmployeeName[] = [];
  allocatiions: EmployeeAllocation[] = [];

  private realmID: string = environment.qboCharityRealmID;

  private formBuilder = inject(FormBuilder);
  private allocationsService = inject(AllocationsService);
  private qbEntityService = inject(QBEntityService);
  private qbEmployeeService = inject(QBEmployeeService);
  private qbPayrollService = inject(QBPayrollService);

  constructor() {
    const invalidClasses = [
      'AFL',
      'EOC',
      '02 Designated Funds',
      '03 Restricted',
    ];
    this.qbEntityService
      .getAllClasses(this.realmID)
      .pipe(
        switchMap((classes) => {
          this.classes = classes.filter(
            (qbClass) => invalidClasses.indexOf(qbClass.value) === -1,
          );
          return this.qbEmployeeService.getAll(this.realmID);
        }),
        switchMap((employees) => {
          this.employees = employees
          return this.qbPayrollService.allocations$
        }),
      )
      .subscribe((allocations) => (this.allocatiions = allocations));
  }

  ngOnInit(): void {
    const formOptions: AbstractControlOptions = {
      //validators: [ProjectAllocationsValidater('allocations')],
    };

    this.form = this.formBuilder.group(
      {
        employees: new FormArray([]),
      },
      formOptions,
    );
  }
}
