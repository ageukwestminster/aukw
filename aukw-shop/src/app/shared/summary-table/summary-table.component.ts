import { Component, Input, OnInit } from '@angular/core';
import { Summary } from '@app/_models';

@Component({
  selector: 'summary-table',
  templateUrl: './summary-table.component.html',
  styleUrls: ['./summary-table.component.css'],
})
export class SummaryTableComponent implements OnInit {
  loading = false;
  @Input() summary!: Summary[];

  constructor() {}

  ngOnInit(): void { }
}
