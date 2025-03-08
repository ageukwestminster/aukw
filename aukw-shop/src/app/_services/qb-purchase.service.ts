import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { ApiMessage, QBPurchase } from '@app/_models';
import { Observable } from 'rxjs';

const baseUrl = `${environment.apiUrl}/qb`;

/**
 * 
 */
@Injectable({ providedIn: 'root' })
export class QBPurchaseService {
  private http = inject(HttpClient);

  create(realmID: string, params: any): Observable<ApiMessage> {
    return this.http.post<ApiMessage>(`${baseUrl}/${realmID}/qb/purchase`, params);
  }

  createNew(txnDate: string,
    bankAccount: [number, string],
    expenseAccount: [number, string],
    entity: [number, string],
    amount: number,
    taxAmount: number = 0,
    description: string = '',
    privateNote: string = '',
  ) : QBPurchase {
    return new QBPurchase({
      txnDate: txnDate,
      bankAccount: bankAccount,
      expenseAccount: expenseAccount,
      entity: entity,
      amount: amount,
      taxAmount: taxAmount,
      description: description,
      privateNote: privateNote,
    });
  }
}
