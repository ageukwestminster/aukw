import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { Shop } from '@app/_models';

const baseUrl = `${environment.apiUrl}/shop`;

/**
 * This class has a single method which returns a array of shops
 * 
 * Shop data is stored in the database.
 * See {@link ../_models/Shop}
 */
@Injectable({ providedIn: 'root' })
export class ShopService {
  constructor(private http: HttpClient) {}

  /**
   * Get a list of the names of all available shops
   * @returns Array of shop objects
   */
  getAll() {
    return this.http.get<Shop[]>(baseUrl);
  }
}
