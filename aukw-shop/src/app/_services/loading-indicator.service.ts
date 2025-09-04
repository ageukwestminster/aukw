import { Component, Injectable, inject, Input } from '@angular/core';

import { Observable } from 'rxjs';
import { defer, tap } from 'rxjs';
import {
  NgbModal,
  NgbActiveModal,
  NgbModalOptions,
  NgbModalRef,
} from '@ng-bootstrap/ng-bootstrap';
import { MessageFactories } from '@app/_interfaces/message-factories';

/**
 * The content for the Modal that displays during a long running task
 *
 * Code from {@link https://medium.com/@andre.schouten_ff/having-some-fun-with-activity-indicators-and-observables-in-angular-dcd1c5ae7685}
 */
@Component({
  standalone: true,
  template: `
    <div class="modal-body">
      <div class="d-flex justify-content-center">
        @if (status == 'loading') {
          <div>
            <span class="spinner-border spinner-border-sm"></span>
          </div>
        }
        @if (status == 'error') {
          <div>
            <i class="fas fa-circle-exclamation" class="red-color"></i>
          </div>
        }
        <p>&nbsp;{{ message }}</p>
      </div>
    </div>
  `,
  styles: ['.red-color { color: red; }'],
  imports: [],
})
export class LoadingIndicatorContent {
  activeModal = inject(NgbActiveModal);
  _status: 'loading' | 'success' | 'error' = 'loading';
  @Input() message: string = '';
  @Input() status: 'loading' | 'success' | 'error' = 'loading';
}

/**
 * Provide a simple way of dispalying an overlay when performing a long running task
 *
 * From {@link https://medium.com/@andre.schouten_ff/having-some-fun-with-activity-indicators-and-observables-in-angular-dcd1c5ae7685 }
 */
@Injectable({ providedIn: 'root' })
export class LoadingIndicatorService {
  /**The length of time in ms to retain the exit messsage before closing the loading modal*/
  readonly DEFAULT_DISMISS_MESSAGE_DURATION: number = 500;

  public constructor(private readonly modalService: NgbModal) {}

  public create(message: string): NgbModalRef {
    // create the loading indicator overlay
    const modalOptions = {
      backdrop: 'static',
      backdropClass: 'loading-indicator-backdrop',
      centered: true,
      fullscreen: 'md',
      size: 'md',
    } as NgbModalOptions;

    const modalRef = this.modalService.open(
      LoadingIndicatorContent,
      modalOptions,
    );
    modalRef.componentInstance.message = message;

    return modalRef;
  }

  public createObserving<T>(
    messageFactories: MessageFactories<T>,
  ): (source: Observable<T>) => Observable<T> {
    return (source) =>
      defer(() => {
        const modalRef = this.create(messageFactories.loading());

        return source.pipe(
          tap({
            next: (value) => {
              modalRef.componentInstance.status = 'success';
              modalRef.componentInstance.message =
                messageFactories.success(value);
              setTimeout(function () {
                modalRef.close();
              }, this.DEFAULT_DISMISS_MESSAGE_DURATION);
            },
            error: (err) => {
              modalRef.componentInstance.status = 'error';
              modalRef.componentInstance.message = messageFactories.error(err);
              setTimeout(function () {
                modalRef.close();
              }, this.DEFAULT_DISMISS_MESSAGE_DURATION * 5);
            },
          }),
        );
      });
  }
}
