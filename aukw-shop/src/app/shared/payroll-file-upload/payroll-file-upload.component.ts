import {
  Component,
  EventEmitter,
  Input,
  inject,
  OnInit,
  Output,
} from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { from, concatMap, tap, Observable } from 'rxjs';
import { NgbModal, NgbTooltip } from '@ng-bootstrap/ng-bootstrap';
import {
  AlertService,
  ConsoleService,
  FileService,
  PayslipListService,
} from '@app/_services';
import { IrisPayslip, UploadResponse } from '@app/_models';
import { PasswordInputModalComponent } from './password-input.component';

@Component({
  selector: 'payroll-file-upload',
  templateUrl: './payroll-file-upload.component.html',
  styleUrls: ['./payroll-file-upload.component.css'],
})
export class PayrollFileUploadComponent implements OnInit {
  /** When 'true' background work is being performed */
  loading: boolean = false;
  /** The file that the user has selected, or null */
  file: File | null = null;
  /**Using a form group to allow me to reset the file input control easily */
  form!: FormGroup;

  @Output() onFileUploaded = new EventEmitter<IrisPayslip[]>();

  /**
   * Input flag to allow disabling the loading of a file until the parent object is ready
   */
  @Input() setButtonDisabled: boolean | null = false;

  private alertService = inject(AlertService);
  private fileService = inject(FileService);
  public modalService = inject(NgbModal);
  private formBuilder = inject(FormBuilder);
  private consoleService = inject(ConsoleService);
  private payslipListService = inject(PayslipListService);

  constructor() {}

  ngOnInit(): void {
    this.form = this.formBuilder.group({ chooseFile: [null] });
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
        this.file = files![0];
      } else {
        this.file = null;
      }
    }

    this.upload();
  }

  upload() {
    if (!this.file) return;

    this.loading = true;
    this.consoleService.sendConsoleMessage(`Uploading '${this.file.name}'.`);

    this.fileService
      .upload(this.file)
      .pipe(
        tap(() => {
          this.consoleService.sendConsoleMessage(
            `Reading '${this.file!.name}' into memory.`,
          );
        }),
        concatMap((response: UploadResponse) => {
          if (response.isEncrypted) {
            return this.decrypt_and_parse(this.file!.name);
          } else {
            return this.fileService.parse(this.file!.name);
          }
        }),
      )
      .subscribe({
        next: (response: IrisPayslip[]) => {
          this.consoleService.sendConsoleMessage(
            `Processing complete for '${this.file!.name}'.`,
          );
          this.payslipListService.sendPayslips(response);
          this.onFileUploaded.emit(response);
        },
        error: (error: any) => {
          this.loading = false;
          /* ignore error if user has cancelled password */
          if (error == 'cancel click') {
            this.file = null;
            this.form.reset();
            return;
          }

          this.consoleService.sendConsoleMessage(
            `Failed to parse '${this.file!.name}'.`,
          );
          this.alertService.error(error, {
            autoClose: false,
          });
        },
        complete: () => (this.loading = false),
      });
  }

  /**
   * Open a modal window to obtain the password, then parse the Spreadsheet
   * @param filename string
   * @returns Open
   */
  private decrypt_and_parse(filename: string): Observable<IrisPayslip[]> {
    const modalRef = this.modalService.open(PasswordInputModalComponent);
    modalRef.componentInstance.fileName = filename;

    return from(modalRef.result).pipe(
      tap(() => this.consoleService.sendConsoleMessage('Decrypting file.')),
      concatMap((password: string) =>
        this.fileService.decrypt(filename, password),
      ),
      tap(() =>
        this.consoleService.sendConsoleMessage(
          'Examining file for pension and salary details.',
        ),
      ),
      concatMap(() => this.fileService.parse()),
    );
  }
}
