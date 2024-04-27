import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';

import { environment } from '@environments/environment';
import { ApiMessage, IrisPayslip, UploadResponse } from '@app/_models';

const baseUrl = `${environment.apiUrl}/xlsx`;

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

    return this.http.post<UploadResponse>(
      `${baseUrl}/upload?filename=${file.name}`,
      formData,
      options,
    );
  }

  decrypt(fileName: string, password: string) {
    return this.http.post<ApiMessage>(
      `${baseUrl}/decrypt?filename=${fileName}`,
      {
        password: password,
      },
    );
  }

  parse(fileName: string = '') {
    if (fileName == '') {
      return this.http.get<IrisPayslip[]>(
        `${baseUrl}/parse`,
      );
    } else {
      return this.http.get<IrisPayslip[]>(
        `${baseUrl}/parse?filename=${fileName}`,
      );
    }
  }
}
