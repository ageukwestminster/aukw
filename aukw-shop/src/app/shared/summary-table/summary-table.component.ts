import { Component, Input, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NgbTooltipModule } from '@ng-bootstrap/ng-bootstrap';
import { Summary } from '@app/_models';

@Component({
  selector: 'summary-table',
  templateUrl: './summary-table.component.html',
  styleUrls: ['./summary-table.component.css'],
  standalone: true,
  imports: [ CommonModule, NgbTooltipModule ],
})
export class SummaryTableComponent implements OnInit {
  loading = false;
  @Input() summary!: Summary[];

  constructor() {}

  ngOnInit(): void {}
}
