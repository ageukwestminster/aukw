import { Component, TemplateRef } from '@angular/core';

import { ToastService } from '@app/_services';

@Component({
  selector: 'app-toasts',
  templateUrl: './toast-container.component.html',
  styleUrls: ['./toast-container.component.css'],
  host: {'[class.ngb-toasts]': 'true'}
})
export class ToastContainerComponent  {

  constructor(public toastService: ToastService) { }

  isTemplate(toast: any) { return toast.textOrTpl instanceof TemplateRef; }
}
