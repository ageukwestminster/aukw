import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import {
  ApiMessage,
  HistogramChartData,
  Takings,
  TakingsSummary,
} from '@app/_models';
import { Observable } from 'rxjs';

const baseUrl = `${environment.apiUrl}/takings`;
const realmID = `${environment.qboEnterprisesRealmID}`;

@Injectable({ providedIn: 'root' })
export class TakingsService {
  constructor(private http: HttpClient) {}

  getByShopID(shopid: number) : Observable<Takings[]> {
    return this.http.get<Takings[]>(`${baseUrl}/shop/${shopid}`);
  }

  getSummary(shopid: number, urlParameters: string) : Observable<TakingsSummary[]> {
    return this.http.get<TakingsSummary[]>(
      `${environment.apiUrl}/report/takingssummary/shop/${shopid}?${urlParameters}`,
    );
  }

  /* return an array of the numdatapoints most recent sales */
  getSimpleSalesList(
    shopid: number,
    numdatapoints: number,
  ): Observable<HistogramChartData> {
    return this.http.get<HistogramChartData>(
      `${environment.apiUrl}/report/saleslist/shop/${shopid}/datapoints/${numdatapoints}`,
    );
  }

  getById(id: number) : Observable<Takings> {
    return this.http.get<Takings>(`${baseUrl}/${id}`);
  }

  getMostRecent(shopid: number): Observable<Takings> {
    return this.http.get<Takings>(`${baseUrl}/most-recent/${shopid}`);
  }

  create(params: any) : Observable<ApiMessage>{
    return this.http.post<ApiMessage>(baseUrl, params);
  }

  update(id: number, params: any) : Observable<ApiMessage> {
    return this.http.put<ApiMessage>(`${baseUrl}/${id}`, params);
  }

  delete(id: number) : Observable<ApiMessage> {
    return this.http.delete<ApiMessage>(`${baseUrl}/${id}`);
  }

  patchQuickbooks(id: number, quickbooksStatus: boolean) : Observable<ApiMessage> {
    const qb_status = { quickbooks: quickbooksStatus ? 1 : 0 };
    return this.http.patch<ApiMessage>(`${baseUrl}/${id}`, qb_status);
  }

  addToQuickbooks(id: number): Observable<ApiMessage> {
    return this.http.post<ApiMessage>(
      `${environment.apiUrl}/qb/${realmID}/salesreceipt/takings/${id}`,
      null,
    );
  }
}
