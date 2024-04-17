import { Component, EventEmitter, Input, Output } from '@angular/core';
import { from, concatMap, iif, tap, Observable } from 'rxjs';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { AlertService, FileService } from '@app/_services';
import { IrisPayslip, UploadResponse } from '@app/_models';
import { PasswordInputModalComponent } from './password-input.component';

@Component({
  selector: 'payroll-file-upload',
  templateUrl: './payroll-file-upload.component.html',
  styleUrls: ['./payroll-file-upload.component.css'],
})
export class PayrollFileUploadComponent {
  status: 'initial' | 'uploading' | 'reading' | 'success' | 'fail' = 'initial';
  file: File | null = null;

  @Output() onFileUploaded: EventEmitter<IrisPayslip[]>;
  /**
   * Input flag to allow disabling the loading of a file until the parent object is ready
   */
  @Input() setButtonDisabled: boolean = false;

  constructor(
    private alertService: AlertService,
    private fileService: FileService,
    public modalService: NgbModal,
  ) {
    this.onFileUploaded = new EventEmitter();
  }

  /**
   * Called when the user chooses a file using the file input control
   * @param event
   * @returns void
   */
  onChange(event: Event): void {
    if (!event) return;

    const files: FileList | null = (event.target as HTMLInputElement).files;
    if (files) {
      if (files.length) {
        this.status = 'initial';
        this.file = files![0];
      } else {
        this.file = null;
      }
    }

    if (!this.file) return;

    const upload$ = this.fileService.upload(this.file).pipe(
      tap(() => {
        this.status = 'reading';
      }),
      concatMap((response: UploadResponse) =>
        iif(
          () => response.isEncrypted,
          this.decrypt_and_parse(this.file!.name),
          this.fileService.parse(),
        ),
      ),
    );

    this.status = 'uploading';

    upload$.subscribe({
      next: (response: IrisPayslip[]) => {
        this.onFileUploaded.emit(response);
        this.status = 'success';
      },
      error: (error: any) => {
        /* ignore error if user has cancelled password */
        if (error == 'cancel click') {
          this.file = null;
          this.status = 'initial';
          return;
        }

        this.status = 'fail';
        this.alertService.error(error, {
          autoClose: false,
        });
      },
    });
  }

  /**
   * Open a modal window to obtain the password, then parse the Spreadsheet
   * @param fileName string
   * @returns Open
   */
  private decrypt_and_parse(fileName: string): Observable<IrisPayslip[]> {
    const modalRef = this.modalService.open(PasswordInputModalComponent);
    modalRef.componentInstance.fileName = fileName;

    return from(modalRef.result).pipe(
      concatMap((password: string) => this.fileService.decrypt(password)),
      concatMap(() => this.fileService.parse()),
    );
  }

  /**
   * A flag representing when there is background work being performed.
   */
  get loading(): boolean {
    return this.status == 'reading' || this.status == 'uploading';
  }
}
