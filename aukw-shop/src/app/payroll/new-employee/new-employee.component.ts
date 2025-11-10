import { Component, inject, Input, OnInit } from '@angular/core';
import { JsonPipe, NgClass } from '@angular/common';
import {
  AbstractControlOptions,
  FormArray,
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';
import { NgbActiveOffcanvas, NgbTooltip } from '@ng-bootstrap/ng-bootstrap';
import { environment } from '@environments/environment';
import { Observable, of, switchMap } from 'rxjs';
import {
  FormMode,
  EmployeeAllocation,
  EmployeeName,
  ValueStringIdPair,
  ApiMessage,
} from '@app/_models';
import {
  AllocationsService,
  QBEmployeeService,
  QBEntityService,
} from '@app/_services';
import { ProjectAllocationsValidater } from '@app/_helpers';

@Component({
  imports: [JsonPipe, NgClass, NgbTooltip, ReactiveFormsModule],
  templateUrl: './new-employee.component.html',
  styleUrl: './new-employee.component.css',
})
export class NewEmployeeComponent implements OnInit {
  form!: FormGroup;
  classes: ValueStringIdPair[] = [];
  employees$: Observable<EmployeeName[]>;
  loading: boolean = false;
  submitted: boolean = false;
  formMode!: FormMode;
  newEmployee: EmployeeName | null = null;

  private realmID: string = environment.qboCharityRealmID;
  private enterprisesRealmID: string = environment.qboEnterprisesRealmID;

  activeOffcanvas = inject(NgbActiveOffcanvas);
  private formBuilder = inject(FormBuilder);
  private allocationsService = inject(AllocationsService);
  private qbEntityService = inject(QBEntityService);
  private qbEmployeeService = inject(QBEmployeeService);

  /** From the Staffology payroll numbers */
  @Input() payrollNumber: number | null = null;
  /** This will only be non-null if the employee has already been added
   * to QuickBooks but not yet assigned allocations.
   */
  @Input() employeeName: EmployeeName | null = null;

  constructor() {
    const invalidClasses = [
      'AFL',
      'EOC',
      '02 Designated Funds',
      '03 Restricted',
    ];
    this.qbEntityService.getAllClasses(this.realmID).subscribe((classes) => {
      this.classes = classes.filter(
        (qbClass) => invalidClasses.indexOf(qbClass.value) === -1,
      );
    });
    this.employees$ = this.qbEmployeeService.getAll(this.realmID);
  }

  ngOnInit(): void {
    const formOptions: AbstractControlOptions = {
      validators: [ProjectAllocationsValidater('allocations')],
    };

    this.form = this.formBuilder.group(
      {
        allocations: new FormArray([]), // Populated later
        quickbooksId: [null],
        payrollNumber: [null, Validators.required],
        firstName: [null, Validators.required],
        lastName: [null, Validators.required],
        isShopEmployee: [false],
      },
      formOptions,
    );

    // Populate form with existing data
    if (this.payrollNumber) {
      this.form.patchValue({
        payrollNumber: this.payrollNumber,
      });
    }
    if (this.employeeName && this.employeeName.payrollNumber) {
      this.form.patchValue({
        quickbooksId: this.employeeName.quickbooksId,
        payrollNumber: this.employeeName.payrollNumber,
        firstName: this.employeeName.firstName,
        lastName: this.employeeName.lastName,
      });
      this.formMode = FormMode.Edit;
    } else {
      this.formMode = FormMode.Add;
    }
    this.onAddAllocation(); // Add one blank allocation
  }

  /** convenience getter for easy access to form fields */
  get f() {
    return this.form.controls;
  }
  /** convenience getter for easy access to allocations FormArray */
  get allocs() {
    return this.f['allocations'] as FormArray;
  }
  /** convenience getter for easy access to form fields within allocations array*/
  get allocationsFormGroups() {
    return this.allocs.controls as FormGroup[];
  }

  onAddAllocation(percentage: number | '' = '', project: string = '') {
    this.allocs.push(
      this.formBuilder.group({
        percentage: [percentage],
        project: [project],
      }),
    );
  }

  onRemoveAllocation(index: number) {
    if (this.allocs.length > 1 && index) {
      this.allocs.removeAt(index);
    }
  }

  onSubmit() {
    this.submitted = true;

    if (this.form.valid) {
      var obs$: Observable<ApiMessage> | null = null;

      switch (this.formMode) {
        case FormMode.Add:
          obs$ = this.onAddEmployee();
          break;
        case FormMode.Edit:
          obs$ = this.appendAllocationsToExistingEmployee();
          break;
      }

      obs$?.subscribe({
        next: () => {
          this.activeOffcanvas.close('Complete');
        },
        error: (error) => {
          console.error('Error creating or saving new Employee:', error);
        },
      });
    }
  }

  private onAddEmployee(): Observable<ApiMessage> {
    var newQBEmployee = {
      givenName: this.f['firstName'].value,
      familyName: this.f['lastName'].value,
      employeeNumber: this.f['payrollNumber'].value,
    };

    return this.qbEmployeeService.create(this.realmID, newQBEmployee).pipe(
      switchMap((message: ApiMessage) => {
        this.newEmployee = new EmployeeName({
          quickbooksId: message.id,
          name: newQBEmployee.givenName + ' ' + newQBEmployee.familyName,
          payrollNumber: newQBEmployee.employeeNumber,
          firstName: newQBEmployee.givenName,
          lastName: newQBEmployee.familyName,
        });

        this.f['quickbooksId'].setValue(message.id);

        if (this.f['isShopEmployee'].value) {
          return this.qbEmployeeService
            .create(this.enterprisesRealmID, newQBEmployee)
            .pipe(switchMap(() => this.appendAllocationsToExistingEmployee()));
        } else {
          return this.appendAllocationsToExistingEmployee();
        }
      }),
    );
  }

  /** Just append allocations */
  private appendAllocationsToExistingEmployee(): Observable<ApiMessage> {
    return this.allocationsService.append(
      this.extractAllocationsFromControls(),
    );
  }

  private extractAllocationsFromControls() {
    if (this.f['isShopEmployee'].value) {
      return [
        new EmployeeAllocation({
          quickbooksId: this.f['quickbooksId'].value,
          payrollNumber: this.f['payrollNumber'].value,
          isShopEmployee: true,
          percentage: 100,
          class: this.classes.find((qbClass) =>
            qbClass.value.toLowerCase().includes('unrestricted'),
          )?.id,
        }),
      ];
    } else {
      return this.allocationsFormGroups
        .map((allocGroup) => {
          return new EmployeeAllocation({
            quickbooksId: this.f['quickbooksId'].value,
            payrollNumber: this.f['payrollNumber'].value,
            isShopEmployee: this.f['isShopEmployee'].value,
            percentage: allocGroup.get('percentage')?.value,
            class: allocGroup.get('project')?.value,
          });
        })
        .filter((allocation) => allocation.class && allocation.class != '');
    }
  }
}
