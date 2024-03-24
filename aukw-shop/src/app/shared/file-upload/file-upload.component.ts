import { Component } from '@angular/core';
import { throwError } from 'rxjs';
import { FileService } from '@app/_services';

@Component({
  selector: 'file-upload',
  templateUrl: './file-upload.component.html',
  styleUrls: ['./file-upload.component.css']
})
export class FileUploadComponent {
  status: "initial" | "uploading" | "success" | "fail" = "initial";
  file:File|null = null;

  constructor(private fileService: FileService) {}

  onChange(event: Event) {

    if (!event) return;

    const files:FileList|null = (event.target as HTMLInputElement).files;
    if (files && files.length) {
      this.status = "initial";
      this.file = files![0];
    }
  }

  onUpload() {

    if (!this.file) return;
    
    const upload$ = this.fileService.upload(this.file);
    this.status = 'uploading';

    upload$.subscribe({
      next: () => {
        this.status = 'success';
      },
      error: (error: any) => {
        this.status = 'fail';
        return throwError(() => error);
      },
    })
}
}
