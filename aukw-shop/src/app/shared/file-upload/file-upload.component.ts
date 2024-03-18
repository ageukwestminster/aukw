import { Component } from '@angular/core';
import { HttpClient } from '@angular/common/http';

//declare var require: any;
//const officeCrypto = require('officecrypto-tool');

@Component({
  selector: 'file-upload',
  templateUrl: './file-upload.component.html',
  styleUrls: ['./file-upload.component.css']
})
export class FileUploadComponent {
  fileName: string = '';

  constructor(private http: HttpClient) {}

  onFileSelected(event: Event) {

    if (!event) return;

    console.log(event!.target!);
    const files:FileList|null = (event.target as HTMLInputElement).files;
    const file:File = files![0];

    let reader = new FileReader();

    reader.onloadend = function() {
      let base64data = reader.result;
      console.log('base64data-', base64data);
      // how to return here?
    };
    
    const input = reader.readAsArrayBuffer(files![0]);
    //const isEncrypted = officeCrypto.isEncrypted(input);

    /*const file:File = {};//event!.target!.files[0];

    if (file) {

        this.fileName = file.name;

        const formData = new FormData();

        formData.append("thumbnail", file);

        const upload$ = this.http.post("/api/thumbnail-upload", formData);

        upload$.subscribe();
    }*/
}
}
