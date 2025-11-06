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
import { Observable } from 'rxjs';
import { FormMode, EmployeeName, ValueStringIdPair } from '@app/_models';
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

  private realmID: string = environment.qboCharityRealmID;

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
      '01 Unrestricted',
      '02 Designated Funds',
      '03 Restricted',
      '04 Administration',
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
        quickbooksId: [null, Validators.required],
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
        percentage: [percentage, [Validators.required]],
        project: [project, [Validators.required]],
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

    switch (this.formMode) {
      case FormMode.Add:
        this.onAddEmployee();
        break;
      case FormMode.Edit:
        this.onEditEmployee();
        break;
    }

    //this.activeOffcanvas.close('Employee Saved')
  }

  private onAddEmployee() {}

  /** Just append allocations */
  private onEditEmployee() {
    console.log('Edit employee allocations');

    var allocationsToAppend = this.allocationsFormGroups.map((allocGroup) => {
      return {
        quickbooksId: this.f['quickbooksId'].value,
        payrollNumber: this.f['payrollNumber'].value,
        isShopEmployee: this.f['isShopEmployee'].value,
        percentage: allocGroup.get('percentage')?.value,
        class: allocGroup.get('project')?.value,
      };
    });

    console.log('Allocations to append:', allocationsToAppend);

    this.allocationsService.append(allocationsToAppend).subscribe({
      next: (response) => {
        console.log('Allocations appended successfully:', response);
        this.activeOffcanvas.close('Employee Allocations Saved');
      },
      error: (error) => {
        console.error('Error appending allocations:', error);
      },
    });
  }
}
