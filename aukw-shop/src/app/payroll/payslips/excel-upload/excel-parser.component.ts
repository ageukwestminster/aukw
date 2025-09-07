import {
  Component,
  EventEmitter,
  inject,
  Output,
  ViewChild,
} from '@angular/core';
import { from, concatMap, of, Observable, catchError } from 'rxjs';
import {
  NgbModal,
  NgbModalModule,
  NgbTooltipModule,
} from '@ng-bootstrap/ng-bootstrap';
import {
  NgbActiveModal,
  NgbModalOptions,
  NgbModalRef,
} from '@ng-bootstrap/ng-bootstrap';
import {
  AlertService,
  FileService,
  PayslipListService,
  ModalService,
} from '@app/_services';
import { IrisPayslip, UploadResponse } from '@app/_models';
import { PasswordInputModalComponent } from './modals/password-input.component';
import { PayrollDateInputModalComponent } from './modals/payrolldate-input.component';
import { ExcelUploadComponent } from './excel-upload.component';

@Component({
  selector: 'excel-upload-and-parse',
  standalone: true,
  imports: [ExcelUploadComponent],
  template:
    '<excel-upload (onFileUploaded)= "onFileUploaded($event)" #uploadComponent></excel-upload>',
})
export class ExcelParserComponent {
  /** When 'true' background work is being performed */
  loading: boolean = false;
  /** The file that the user has selected, or null */
  file: File | null = null;

  @ViewChild('uploadComponent') uploadComponent:
    | ExcelUploadComponent
    | undefined;

  @Output() onPayslipsProduced = new EventEmitter<IrisPayslip[]>();

  private alertService = inject(AlertService);
  private fileService = inject(FileService);
  public modalService = inject(ModalService); // A wrapper for NgbModal to avoid aria-hidden warnings
  private payslipListService = inject(PayslipListService);

  onFileUploaded(file: File) {
    this.loading = true;

    this.fileService
      .upload(file)
      .pipe(
        concatMap((response: UploadResponse) => {
          if (response.isEncrypted) {
            return this.decrypt_and_parse(file!.name);
          } else {
            return this.just_parse(file!.name);
          }
        }),
      )
      .subscribe({
        next: (response: IrisPayslip[]) => {
          this.payslipListService.sendPayslips(response);
          this.onPayslipsProduced.emit(response);
        },
        error: (error: any) => {
          this.loading = false;
          /* ignore error if user has cancelled password */
          if (error == 'cancel click') {
            return;
          }

          this.alertService.error(error, {
            autoClose: false,
          });
        },
        complete: () => (this.loading = false),
      });
  }

  /**
   * Open a modal window to obtain the password, then parse the Spreadsheet
   * @param filename string The file to parse.
   * @returns An Observable of an array of employee payslips
   */
  private decrypt_and_parse(filename: string): Observable<IrisPayslip[]> {
    const modalRef = this.modalService.open(PasswordInputModalComponent);
    modalRef.componentInstance.fileName = filename;

    return from(modalRef.result).pipe(
      concatMap((password: string) =>
        this.fileService.decrypt(filename, password),
      ),
      catchError((err) => {
        if (err == 'cancel click') {
          this.uploadComponent?.removeFile();
        }
        return of();
      }),
      concatMap(() => this.fileService.parse()),
    );
  }

  /**
   * Parse the Spreadsheet into Pasyslip objects
   * @param filename string The file to parse.
   * @returns An Observable of an array of employee payslips
   */
  private just_parse(filename: string): Observable<IrisPayslip[]> {
    const modalRef = this.modalService.open(PayrollDateInputModalComponent);

    return from(modalRef.result).pipe(
      concatMap((payrollDate: string) =>
        this.fileService.parse(filename, payrollDate),
      ),
      catchError((err) => {
        if (err == 'cancel click') {
          this.uploadComponent?.removeFile();
        }
        return of();
      }),
    );
  }
}
