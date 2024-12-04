import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, NgFor } from '@angular/common';
import { AuditLogService } from '@app/_services';
import { AuditLog, AuditLogFilter } from '@app/_models';
import { AuditLogFilterComponent } from './filter/auditlog-filter.component';

@Component({
  standalone: true,
  imports: [    
    CommonModule,
    NgFor,
    AuditLogFilterComponent,
  ],
  templateUrl: 'auditlog.component.html'
})
export class AuditLogComponent implements OnInit {
  auditLog!: AuditLog[]; 
  loading: boolean = false;
  filter!: AuditLogFilter;

  private auditLogService = inject(AuditLogService);

  constructor() {}

  ngOnInit() {
    this.refreshList();
  }

  refreshList() {
    this.auditLogService
      .getAll()
      .subscribe( (result) => {
        this.auditLog = result;
      });
  }

  filterUpdated(filter: AuditLogFilter) {
    this.filter = filter;
  }

  filterIsLoading(value: boolean) {
    this.loading = value;
  }

  auditLogUpdated(auditLog: AuditLog[]) {
    this.auditLog = auditLog;
  }
}
