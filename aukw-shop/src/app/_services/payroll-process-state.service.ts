import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { concatMap, filter, map, scan } from 'rxjs/operators';
import { PayrollProcessState } from '@app/_models';


/**
 * This class separates out payslip functions
 */
@Injectable({ providedIn: 'root' })
export class PayrollProcessStateService {
  
    public stateSubject = new BehaviorSubject<PayrollProcessState>(PayrollProcessState.INITIAL);

    public state: Observable<PayrollProcessState>;

    public setState(value: PayrollProcessState) {
        this.stateSubject.next(value);
    }

    constructor() {
        this.state = this.stateSubject.asObservable();
    }

}