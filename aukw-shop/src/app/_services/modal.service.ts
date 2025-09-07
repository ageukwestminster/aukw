import { Injectable, inject } from '@angular/core';
import {
  NgbModal,
  NgbModalOptions,
  NgbModalRef,
} from '@ng-bootstrap/ng-bootstrap';

/**
 * This class has a single method which opens an NgbModal in
 * such a way that the 'aria-hidden' warning is not shown.
 * It is a wrapper for NgbModal
 */
@Injectable({ providedIn: 'root' })
export class ModalService {
  private modalService = inject(NgbModal);

  /**
   * Opens a new modal window with the specified content and supplied options.
   */
  open(content: any, options?: NgbModalOptions): NgbModalRef {
    // Added to remove focus from any button that might have been clicked to start the process
    // which would otherwise remain focused behind the modal and caused an aria-hidden warning in
    // modern browsers. From https://stackoverflow.com/a/79210442
    const buttonElement = document.activeElement as HTMLElement; // Get the currently focused element
    buttonElement.blur(); // Remove focus from the button

    // Open the modal
    return this.modalService.open(content, options);
  }
}
