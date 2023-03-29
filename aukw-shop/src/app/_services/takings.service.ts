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

@Injectable({ providedIn: 'root' })
export class TakingsService {
  constructor(private http: HttpClient) {}

  getByShopID(shopid: number) {
    return this.http.get<Takings[]>(`${baseUrl}/shop/${shopid}`);
  }

  getSummary(shopid: number, urlParameters: string) {
    return this.http.get<TakingsSummary[]>(`${baseUrl}/summary/shop/${shopid}?${urlParameters}`);
  }

  /* return an array of the numdatapoints most recent sales */
  getSimpleSalesList(shopid: number, numdatapoints: number) {
    return this.http.get<HistogramChartData>(
      `${baseUrl}/saleslist/shop/${shopid}/datapoints/${numdatapoints}`
    );
  }

  getById(id: number) {
    return this.http.get<Takings>(`${baseUrl}/${id}`);
  }

  getMostRecent(shopid: number) {
    return this.http.get<Takings>(`${baseUrl}/most-recent/${shopid}`);
  }

  create(params: any) {
    return this.http.post(baseUrl, params);
  }

  update(id: number, params: any) {
    return this.http.put(`${baseUrl}/${id}`, params);
  }

  delete(id: number) {
    return this.http.delete(`${baseUrl}/${id}`);
  }

  patchQuickbooks(id: number, quickbooksStatus: boolean) {
    const qb_status = { quickbooks: quickbooksStatus ? 1 : 0 };
    return this.http.patch(`${baseUrl}/${id}`, qb_status);
  }

  addToQuickbooks(id: number): Observable<ApiMessage> {
    return this.http.post<ApiMessage>(
      `${environment.apiUrl}/qb/salesreceipt/takings/${id}`,
      null
    );
  }
}
