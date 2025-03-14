import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { Observable } from 'rxjs';
import {
  QBAccountListEntry,
  ProfitAndLossData,
  InStoreSalesData,
  RaggingQuarter,
  SalesByItem,
} from '@app/_models';

const baseUrl = `${environment.apiUrl}/qb`;

/**
 * This class is used to retrieve reports from QBO
 */
@Injectable({ providedIn: 'root' })
export class QBReportService {
  private http = inject(HttpClient);

  /**
   * Get a list of transactions for the AUEW/AUKW inter company account
   * in the Enterprises or Charity company file.
   * @param string start The start date for the report
   * @param string end The end date for the report, must be a date after start
   * @param boolean enterprises (optional) if 'true' then run the report
   * against the Enterprises company file, otherwise use the Charity
   * company file. Defaults to Enterprises.
   * @returns Array of QBAccountListEntry
   */
  getIntercoAccountLedger(
    start: string,
    end: string,
    enterprises: boolean = true,
  ): Observable<QBAccountListEntry[]> {
    let realmId = environment.qboEnterprisesRealmID;
    let accountId = environment.qboEnterprisesIntercompanyAccount;
    if (!enterprises) {
      realmId = environment.qboCharityRealmID;
      accountId = environment.qboCharityIntercompanyAccount;
    }
    return this.http.get<QBAccountListEntry[]>(
      `${baseUrl}/${realmId}/report/generalledger` +
        `?start=${start}&end=${end}&account=${accountId}&sortDescending`,
    );
  }

  /**
   * Get a report that can be used to complete the Charity Retail
   * Association quarterly QMA request
   * @param string start The start date for the report
   * @param string end The end date for the report, must be a date after start
   * @returns object
   */
  getInStoreSales(start: string, end: string): Observable<InStoreSalesData> {
    let realmId = environment.qboEnterprisesRealmID;
    return this.http.get<InStoreSalesData>(
      `${baseUrl}/${realmId}/report/qma` + `?start=${start}&end=${end}`,
    );
  }

  /**
   * Get a profit and loss report for the given dates
   * Association quarterly QMA request
   * @param string start The start date for the report
   * @param string end The end date for the report, must be a date after start
   * @returns object
   */
  getPandLReport(
    start: string,
    end: string,
    enterprises: boolean = true,
  ): Observable<ProfitAndLossData> {
    if (!start) {
      throw new Error('Start date is missing');
    }

    if (!end) {
      throw new Error('End date is missing');
    }

    let realmId = environment.qboEnterprisesRealmID;
    if (!enterprises) {
      realmId = environment.qboCharityRealmID;
    }
    return this.http.get<ProfitAndLossData>(
      `${baseUrl}/${realmId}/report/profitandloss` +
        `?start=${start}&end=${end}`,
    );
  }

  /**
   * Get a report that can be used to complete the Charity Retail
   * Association quarterly QMA request. it shows sales grouped by item (aka Product)
   * @param string start The start date for the report
   * @param string end The end date for the report, must be a date after start
   * @returns Observable<SalesByItem[]>
   */
  getSalesByItem(start: string, end: string): Observable<SalesByItem[]> {
    let realmId = environment.qboEnterprisesRealmID;
    return this.http.get<SalesByItem[]>(
      `${baseUrl}/${realmId}/report/salesbyitem` + `?start=${start}&end=${end}`,
    );
  }

  /**
   * Get a report of ragging receipts, by quarter
   * @param string start The start date for the report, if not supplied then the
   * first date of the quarter that started 5 years ago is used.
   * @returns Observable<RaggingQuarter[]>
   */
  raggingByQuarter(start: string = ''): Observable<RaggingQuarter[]> {
    let realmId = environment.qboEnterprisesRealmID;
    //let url = `${baseUrl}/${realmId}/report/ragging-by-quarter${start?'/?start='+start:''}`;
    return this.http.get<RaggingQuarter[]>(
      `${baseUrl}/${realmId}/report/ragging-by-quarter${start ? '/?start=' + start : ''}`,
    );
  }
}
