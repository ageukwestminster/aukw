import { Component, inject, OnInit } from '@angular/core';
import { Router, ActivatedRoute, Event, NavigationEnd, NavigationError } from '@angular/router';
import { EmployeeAllocations, FormMode, QBClass } from '@app/_models';

@Component({
  selector: 'app-add-edit',
  imports: [],
  templateUrl: './add-edit.component.html',
  styleUrl: './add-edit.component.css',
})
export class AllocationsAddEditComponent implements OnInit {
  
  employeeAllocs: EmployeeAllocations | null = null;
  classes: QBClass[] = [];
  payrollNumber!: number;
  formMode: FormMode = FormMode.Add;

  // Need
  //classes
  //emplo

  private route = inject(ActivatedRoute);
  private router = inject(Router);

  constructor() {
    //Dummy Data
    this.employeeAllocs = JSON.parse(
      '{"name":{"quickbooksId":423,"name":"Carly Connolly",' +
        '"payrollNumber":39,"firstName":"Carly","lastName":"Connolly","middleName":"Jayne"}' +
        ',"projects":[{"percentage":40,"classID":"1400000000000130722"},{"percentage":60,"c' +
        'lassID":"1400000000000130711"}]}',
    );
  }
    ngOnInit() {
      this.payrollNumber = this.route.snapshot.params['id'];
      if (this.payrollNumber) {
        this.formMode = FormMode.Edit;
      }

        this.router.events.subscribe((event: Event) => {

            if (event instanceof NavigationEnd) {
              this.payrollNumber = this.route.snapshot.params['id'];
              if (this.payrollNumber) console.log(`PayrollNumber: ${this.payrollNumber}`)
            }

            if (event instanceof NavigationError) {
                // Hide loading indicator

                // Present error to user
                console.log(event.error);
            }
        });
    }
}
