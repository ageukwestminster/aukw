import { Component, Input, OnChanges, SimpleChanges } from '@angular/core';
import { CommonModule, NgFor, NgIf } from '@angular/common';
import { IrisPayslip, LineItemDetail } from '@app/_models';
import { from, scan } from 'rxjs';
import { NgbTooltip } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'allocated-costs-list',
  standalone: true,
  imports: [CommonModule, NgbTooltip, NgFor, NgIf],
  templateUrl: './list.component.html',
  styleUrls: ['./list.component.css', '../../shared.css'],
})
export class AllocatedCostsListComponent implements OnChanges {
  total: number = 0;

  @Input() title!: string;
  @Input() transactionType!: string;
  @Input() lines: LineItemDetail[] = [];
  @Input() payslips: IrisPayslip[] = [];
  @Input() inQBOProperty!: (p: IrisPayslip) => boolean;

  ngOnChanges(changes: SimpleChanges): void {
    if (!this.payslips.length || !this.lines.length) return;

    this.total = 0;

    from(this.lines)
      .pipe(scan((a: number, v: LineItemDetail) => a + v.amount, 0))
      .subscribe((total: number) => (this.total = total));
  }

  /** Present the string 'Charity Shop' for the class when the class is '01 Unrestricted'.
   * Can do this because only the shop emnployees have that class allocation.
   */
  className(name_from_quickbooks: string): string {
    return name_from_quickbooks.startsWith('01')
      ? 'Charity Shop'
      : name_from_quickbooks;
  }

  /**
   * Check if the values contianed in the given LineItemDetail have been flagged as having already been
   * entered in QuickBooks.
   * @param line The details of the entry
   * @returns 'True' if already in QBO.
   */
  inQBO(line: LineItemDetail): boolean {
    if (!this.payslips || !this.payslips.length) return false;
    return (
      this.payslips.filter(
        (p) => p.payrollNumber == line.payrollNumber && this.inQBOProperty(p),
      ).length != 0
    );
  }
}
