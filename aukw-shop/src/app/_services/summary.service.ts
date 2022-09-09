import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { Summary } from '@app/_models';
import { Chart } from '@app/_models';

const baseUrl = `${environment.apiUrl}/summary`;
const chartUrl = baseUrl+`/chart`;

@Injectable({ providedIn: 'root' })
export class SummaryService {
    constructor(private http: HttpClient) { }

    getSummary() {
        return this.http.get<Summary[]>(baseUrl);
    }

    getChartData() {
        return this.http.get<Chart[]>(chartUrl);
    }

}