import { Component,signal, ViewChild, ElementRef } from '@angular/core';
import { NgIf } from '@angular/common';

@Component({
  selector: 'excel-upload',
  standalone: true,
  imports: [ NgIf ],
  templateUrl: './excel-upload.component.html',
  styleUrl: './excel-upload.component.css'
})
export class ExcelUploadComponent {

  fileName = signal('');
  fileSize = signal(0);
  fileTypeIsCSV = signal(false);
  @ViewChild('fileInput') fileInput: ElementRef | undefined;
  selectedFile: File | null = null;
  uploadSuccess: boolean = false;
  uploadError: boolean = false;

  readonly XLSX_FILETYPE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
  readonly XLS_FILETYPE = 'application/vnd.ms-excel';

  constructor() {}

  // Handler for file input change
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
    if (file && (file.type.endsWith('csv') 
                  || file.type == this.XLSX_FILETYPE
                  || file.type == this.XLS_FILETYPE)) {
      this.selectedFile = file;
      this.fileSize.set(Math.round(file.size / 1024)); // Set file size in KB
      if (file.type.endsWith('csv') ) this.fileTypeIsCSV.set(true);

      this.uploadSuccess = true;
      this.uploadError = false;
      this.fileName.set(file.name);

      //TODO Output file data 
    } else {
      this.uploadSuccess = false;
      this.uploadError = true;
      
      //TODO Add alert msg
    }
  }

  // Method to remove the uploaded file
  removeFile(): void {
    this.selectedFile = null;
    this.fileName.set('');
    this.fileSize.set(0);
    this.uploadSuccess = false;
    this.uploadError = false;
  }

}
