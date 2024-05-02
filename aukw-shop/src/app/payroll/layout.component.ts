import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';

@Component({
  selector: 'reports-layout-component',
  templateUrl: 'layout.component.html',
  standalone: true,
  imports: [ RouterOutlet ],
})
export class PayrollLayoutComponent {}