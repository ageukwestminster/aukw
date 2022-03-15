import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { Summary } from '@app/_models';

const baseUrl = `${environment.apiUrl}/summary`;

@Injectable({ providedIn: 'root' })
export class SummaryService {
    constructor(private http: HttpClient) { }

    getSummary() {
        return this.http.get<Summary[]>(baseUrl);
    }

}