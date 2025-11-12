import { Component, Input } from '@angular/core';
import { EmployeeAllocations } from '@app/_models';

@Component({
  selector: 'app-add-edit',
  imports: [],
  templateUrl: './add-edit.component.html',
  styleUrl: './add-edit.component.css',
})
export class AllocationsAddEditComponent {
  //@Input() employeeAllocs: EmployeeAllocations | null = null;
  employeeAllocs: EmployeeAllocations | null = null;

  constructor() {
    //Dummy Data
    this.employeeAllocs = JSON.parse(
      '{"name":{"quickbooksId":423,"name":"Carly Connolly",' +
        '"payrollNumber":39,"firstName":"Carly","lastName":"Connolly","middleName":"Jayne"}' +
        ',"projects":[{"percentage":40,"classID":"1400000000000130722"},{"percentage":60,"c' +
        'lassID":"1400000000000130711"}]}',
    );
  }
}
