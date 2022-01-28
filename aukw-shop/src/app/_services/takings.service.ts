import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { Takings, TakingsSummary } from '@app/_models';

const baseUrl = `${environment.apiUrl}/takings`;

@Injectable({ providedIn: 'root' })
export class TakingsService {
    constructor(private http: HttpClient) { }

    getByShopID(shopid: number) {
        return this.http.get<Takings[]>(`${baseUrl}/shop/${shopid}`);
    }

    getSummary(shopid: number) {
        return this.http.get<TakingsSummary[]>(`${baseUrl}/summary/shop/${shopid}`);
    }

    getById(id: number) {
        return this.http.get<Takings>(`${baseUrl}/${id}`);
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
}