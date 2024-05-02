import { Component } from '@angular/core';
import { NgIf } from '@angular/common';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { NgbNavModule } from '@ng-bootstrap/ng-bootstrap';

@Component({
  templateUrl: 'frontpage.component.html',
  standalone: true,
  imports: [ NgbNavModule, NgIf, RouterLink, RouterLinkActive ],
  styleUrl: './frontpage.component.css',
})
export class PayrollFrontPageComponent {}