/// <reference types="@angular/localize" />
import { bootstrapApplication } from '@angular/platform-browser';
import { provideRouter } from '@angular/router';
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { enableProdMode } from '@angular/core';
import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';

import { AppComponent } from './app/app.component';
import { jwtInterceptor, errorInterceptor } from '@app/_helpers';
import { environment } from './environments/environment';
import { APP_ROUTES } from '@app/app.routes';

if (environment.production) {
  enableProdMode();
}

bootstrapApplication(AppComponent, {
  providers: [
    provideRouter(APP_ROUTES),
    provideHttpClient(withInterceptors([jwtInterceptor, errorInterceptor])),
  ],
});
