import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, Location, NgFor } from '@angular/common';
import { AuditLogService } from '@app/_services';
import { AuditLog } from '@app/_models';
import { AuditLogFilterComponent } from './filter/auditlog-filter.component';

@Component({
  standalone: true,
  imports: [CommonModule, NgFor, AuditLogFilterComponent],
  templateUrl: 'auditlog.component.html',
})
export class AuditLogComponent implements OnInit {
  auditLog!: AuditLog[];

  private auditLogService = inject(AuditLogService);
  private location = inject(Location);

  constructor() {}

  ngOnInit() {
    this.refreshList();
  }

  refreshList() {
    this.auditLogService.getAll().subscribe((result) => {
      this.auditLog = result;
    });
  }

  auditLogUpdated(auditLog: AuditLog[]) {
    this.auditLog = auditLog;
  }

  /** Return to previous page */
  goBack() {
    this.location.back();
    return false; // don't propagate event
  }
}
