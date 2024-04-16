import { Component, EventEmitter, Input, Output } from '@angular/core';
import { from, throwError, concatMap, iif, tap } from 'rxjs';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { AlertService, FileService } from '@app/_services';
import { IrisPayslip, UploadResponse } from '@app/_models';
import { PasswordInputModalComponent } from './password-input.component';

@Component({
  selector: 'file-upload',
  templateUrl: './file-upload.component.html',
  styleUrls: ['./file-upload.component.css'],
})
export class FileUploadComponent {
  status: 'initial' | 'uploading' | 'reading' | 'success' | 'fail' = 'initial';
  file: File | null = null;
  @Output() onFileUploaded: EventEmitter<IrisPayslip[]>;
  @Input() setButtonDisabled: boolean = false;

  constructor(
    private alertService: AlertService,
    private fileService: FileService,
    public modalService: NgbModal,
  ) {
    this.onFileUploaded = new EventEmitter();
  }

  onChange(event: Event) {
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

    const decrypt_and_parse$ = from(
      this.modalService.open(PasswordInputModalComponent).result,
    ).pipe(
      concatMap((password: string) => this.fileService.decrypt(password)),
      concatMap(() => this.fileService.parse()),
    );

    const just_parse$ = this.fileService.parse();

    const upload$ = this.fileService.upload(this.file).pipe(
      tap(() => {
        this.status = 'reading';
      }),
      concatMap((response: UploadResponse) =>
        iif(() => response.isEncrypted, decrypt_and_parse$, just_parse$),
      ),
    );

    this.status = 'uploading';

    upload$.subscribe({
      next: (response: IrisPayslip[]) => {
        this.onFileUploaded.emit(response);
        this.status = 'success';
      },
      error: (error: any) => {
        this.status = 'fail';
        this.alertService.error(error, {
          autoClose: false,
        });
        return throwError(() => error);
      },
    });
  }

  get loading(): boolean {
    return this.status == 'reading' || this.status == 'uploading';
  }
}
