import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';

import { environment } from '@environments/environment';

const baseUrl = `${environment.apiUrl}/xlsx/upload`;

/**
 * Used to upload files to the API
 */
@Injectable({ providedIn: 'root' })
export class FileService {
  
  constructor(private http: HttpClient) {}


  upload(file: File) {

    let formData = new FormData();
    formData.append('file', file, file.name);

    let params = new HttpParams();

    const options = {
      params: params,
      reportProgress: true,
    };

    return this.http.post(baseUrl,formData, options);
  }
}
