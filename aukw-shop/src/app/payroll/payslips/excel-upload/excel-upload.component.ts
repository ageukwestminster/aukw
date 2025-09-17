import {
  Component,
  EventEmitter,
  inject,
  Output,
  signal,
  ViewChild,
  ElementRef,
} from '@angular/core';

import { AlertService } from '@app/_services';

@Component({
  selector: 'excel-upload',
  standalone: true,
  imports: [],
  templateUrl: './excel-upload.component.html',
  styleUrl: './excel-upload.component.css',
})
/**
 * Code from https://medium.com/@paul.pietzko/custom-file-uploader-angular-18-ca566131f128
 */
export class ExcelUploadComponent {
  fileName = signal('');
  fileSize = signal(0);
  fileTypeIsCSV = signal(false);

  /**
   * Reference to the file input element in the template
   * @ViewChild makes it possible to access native DOM elements that have a template reference variable.
   */
  @ViewChild('fileInput') fileInput: ElementRef | undefined;

  selectedFile: File | null = null;
  uploadSuccess: boolean = false;
  uploadError: boolean = false;

  readonly XLSX_FILETYPE =
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
  readonly XLS_FILETYPE = 'application/vnd.ms-excel';

  @Output() onFileUploaded = new EventEmitter<File>();

  private alertService = inject(AlertService);

  constructor() {}

  /**
   * Called when the user cancels the file input control
   * @param event
   * @returns void
   */
  onCancel(event: Event): void {
    if (!event) return;
    this.removeFile();
  }

  /**
   * Handler for file input change
   * @param event
   */
  onFileChange(event: any): void {
    const file = event.target.files[0] as File | null;
    this.uploadFile(file);
  }

  // Handler for file drop
  onFileDrop(event: DragEvent): void {
    event.preventDefault();
    const file = event.dataTransfer?.files[0] as File | null;
    this.uploadFile(file);
  }

  // Prevent default dragover behavior
  onDragOver(event: DragEvent): void {
    event.preventDefault();
  }

  // Method to handle file upload
  uploadFile(file: File | null): void {
    if (
      file &&
      (file.type.endsWith('csv') ||
        file.type == this.XLSX_FILETYPE ||
        file.type == this.XLS_FILETYPE)
    ) {
      this.selectedFile = file;
      this.fileSize.set(Math.round(file.size / 1024)); // Set file size in KB
      if (file.type.endsWith('csv')) this.fileTypeIsCSV.set(true);

      this.uploadSuccess = true;
      this.uploadError = false;
      this.fileName.set(file.name);

      this.onFileUploaded.emit(file);
    } else {
      this.uploadSuccess = false;
      this.uploadError = true;

      if (file) {
        this.alertService.error(
          'The file type is incorrect. It must be one of ' +
            'CSV, XLS or XLSX. Please upload a different file.',
          {
            autoClose: true,
          },
        );
      }
    }
  }

  // Method to remove the uploaded file
  removeFile(): void {
    this.selectedFile = null;
    this.fileName.set('');
    this.fileSize.set(0);
    this.uploadSuccess = false;
    this.uploadError = false;
    this.fileInput!.nativeElement.value = '';
  }
}
