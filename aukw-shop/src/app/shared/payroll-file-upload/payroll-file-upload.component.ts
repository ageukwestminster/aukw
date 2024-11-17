import {
  AfterViewInit,
  AfterContentInit,
  Component,
  ElementRef,
  EventEmitter,
  Input,
  inject,
  OnInit,
  Output,
  ViewChild,
} from '@angular/core';
import { CommonModule, NgIf } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';
import { from, concatMap, tap, Observable } from 'rxjs';
import {
  NgbModal,
  NgbModalModule,
  NgbTooltipModule,
} from '@ng-bootstrap/ng-bootstrap';
import {
  AlertService,
  ConsoleService,
  FileService,
  PayslipListService,
} from '@app/_services';
import { IrisPayslip, UploadResponse } from '@app/_models';
import { PasswordInputModalComponent } from './modals/password-input.component';
import { PayrollDateInputModalComponent } from './modals/payrolldate-input.component';

@Component({
  selector: 'payroll-file-upload',
  templateUrl: './payroll-file-upload.component.html',
  styleUrls: ['./payroll-file-upload.component.css'],
  standalone: true,
  imports: [
    CommonModule,
    NgbModalModule,
    NgbTooltipModule,
    ReactiveFormsModule,
  ],
})
export class PayrollFileUploadComponent implements AfterViewInit, OnInit, AfterContentInit {
  /** When 'true' background work is being performed */
  loading: boolean = false;
  /** The file that the user has selected, or null */
  file: File | null = null;
  /**Using a form group to allow me to reset the file input control easily */
  form!: FormGroup;

  @Output() onFileUploaded = new EventEmitter<IrisPayslip[]>();
  @Output() onFileUploadCancelled = new EventEmitter();

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

  @ViewChild('fileUpload') fileUploadRef!: ElementRef;

  constructor() {}

  ngOnInit(): void {
    console.log('OnInit');
    this.form = this.formBuilder.group({ chooseFile: [null] });
  }

  /**
   * This lifecycle hook that is called after Angular has fully initialized a component's view
   */
  ngAfterViewInit(): void {
    console.log('AfterViewInit');
    // 'click' the file upload input to show the file open dialog immediately upon init
    this.fileUploadRef.nativeElement.click();
  }

  ngAfterContentInit(): void {
    console.log('AfterContentInit');
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

    /**
   * Called when the user cancels the file input control
   * @param event
   * @returns void
   */
  onCancel(event: Event): void {
    if (!event) return;

    this.file = null;
    this.form.reset();
    this.onFileUploadCancelled.emit();
  }

  /**
   * Upload a selected file to via the API to the server and then decrypt and parse the file.
   * Called when user selects a file to upload.
   * @returns void
   */
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
            return this.just_parse(this.file!.name);
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
   * @param filename string The file to parse.
   * @returns An Observable of an array of employee payslips
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
      tap(() =>
        this.consoleService.sendConsoleMessage(
          'Examining file for pension and salary details.',
        ),
      ),
    );
  }
}
