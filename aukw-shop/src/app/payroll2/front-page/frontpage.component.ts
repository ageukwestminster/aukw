import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { NgbNavModule } from '@ng-bootstrap/ng-bootstrap';

@Component({
  templateUrl: 'frontpage.component.html',
  standalone: true,
  imports: [NgbNavModule, RouterOutlet],
  styleUrl: './frontpage.component.css',
})
export class PayrollFrontPageComponent {
  constructor() {}
}
