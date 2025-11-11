import { Component, EventEmitter, Input, Output } from '@angular/core';
import { NgClass } from '@angular/common';
import { FormArray, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { EmployeeAllocation, ValueStringIdPair } from '@app/_models';

@Component({
  selector: 'allocation-row',
  imports: [NgClass, ReactiveFormsModule],
  templateUrl: './allocation-row.component.html',
  styleUrl: './allocation-row.component.css',
})
export class AllocationRowComponent {
  @Input() allocation!: FormGroup;
  @Input() classes: ValueStringIdPair[] = [];
  @Input() submitted: boolean = false;
  @Input() loading: boolean = false;
  @Input() rowNumber: number = 0;

  @Output() onRemoveAllocation: EventEmitter<number> =
    new EventEmitter<number>();

  removeAllocation(index: number) {
    this.onRemoveAllocation.emit(index);
  }
}
