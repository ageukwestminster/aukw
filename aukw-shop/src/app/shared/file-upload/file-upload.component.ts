import { Component, EventEmitter, Output } from '@angular/core';
import { throwError, concatMap } from 'rxjs';
import { FileService } from '@app/_services';
import { IrisPayslip } from '@app/_models';

@Component({
  selector: 'file-upload',
  templateUrl: './file-upload.component.html',
  styleUrls: ['./file-upload.component.css']
})
export class FileUploadComponent {
  status: "initial" | "uploading" | "reading" | "success" | "fail" = "initial";
  file:File|null = null;
  @Output() onFileUploaded: EventEmitter<IrisPayslip[]>;

  constructor(private fileService: FileService) {
    this.onFileUploaded = new EventEmitter();
  }

  onChange(event: Event) {

    if (!event) return;

    const files:FileList|null = (event.target as HTMLInputElement).files;
    if (files && files.length) {
      this.status = "initial";
      this.file = files![0];
    }

    if (!this.file) return;
    
    const upload$ = this.fileService.upload(this.file)
      .pipe(
        concatMap(() => {   
          this.status = 'reading';       
          return this.fileService.decrypt('FMP804');
        }),
        concatMap(() => { 
          return this.fileService.parse();
        }),
      );
    this.status = 'uploading';

    upload$.subscribe({
      next: (response:IrisPayslip[]) => {        
        this.onFileUploaded.emit(response);
        this.status = 'success';
      },
      error: (error: any) => {
        this.status = 'fail';
        return throwError(() => error);
      },
    })
}
}
