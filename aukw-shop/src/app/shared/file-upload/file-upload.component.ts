import { Component, EventEmitter, Output } from '@angular/core';
import { throwError, concatMap, iif, tap } from 'rxjs';
import { FileService } from '@app/_services';
import { IrisPayslip,UploadResponse } from '@app/_models';

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

    const decrypt_and_parse$ = this.fileService.decrypt('FMP804')
      .pipe(
        concatMap(() => { 
          return this.fileService.parse();
        })
      );
      const just_parse$ = this.fileService.parse();
    
    const upload$ = this.fileService.upload(this.file)
      .pipe(
        tap(() => {this.status = 'reading';}),
        concatMap((response: UploadResponse) => iif(() => 
              response.isEncrypted,decrypt_and_parse$,just_parse$))
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
