import { Injectable, TemplateRef } from '@angular/core';

@Injectable({ providedIn: 'root' })
export class ToastService {
  toasts: any[] = [];

  show(textOrTpl: string | TemplateRef<any>, options: any = {}) {
    this.toasts.push({ textOrTpl, ...options });
  }

  remove(toast: any) {
    this.toasts = this.toasts.filter(t => t !== toast);
  }

  /* 
    Some Sample method calls:

    this.toastService.show('I am a standard toast');
    this.toastService.show('I am a success toast', 
                { classname: 'bg-success text-light', delay: 10000 });
    this.toastService.show(dangerTpl, { classname: 'bg-danger text-light', delay: 15000 });
  */
}